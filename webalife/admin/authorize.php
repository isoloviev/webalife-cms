<?php

session_start();

if (isset($_SESSION['AdminPanel'])) {
    session_name("AdminPanel");
    header("Location: index.php");
    exit;
}

require_once('../../wl_config.php');
require_once(CMS_LANGUAGES . "language_admin_" . DEFAULT_LANG . ".php");

include_once(CMS_CLASSES . 'template.class.php');
include_once(CMS_CLASSES . 'mysql.class.php');
$db = new mysql();
$db->connect();

$tpl = new template();
$validationFailed = false;

if ($_POST['login'] != "") {
    $login = $_POST['login'];
    $password = md5($_POST['pswrd']);
    $realpswrd = ADMIN_PSWRD;
    if ($login == "Administrator" && $password == $realpswrd) {
        session_name("AdminPanel");
        $_SESSION['InAdminSession'] = true;
        $_SESSION['user'] = $login;
        $_SESSION['password'] = $password;

        $_SESSION['ADMIN_ID'] = -1;
        $_SESSION['AdminRoot'] = true;

        if ($_POST['remember']) {
            setcookie("WL_LOGIN_HASH", md5($password), (time() + 60 * 60 * 24 * 30));
        } else {
            setcookie("WL_LOGIN_HASH");
        }
        setcookie('mkse', $login, (time() + 60 * 60 * 24 * 30), '/');
        setcookie('mkse_login', 1, 0, '/');

        $_SESSION['SITE_ID'] = $_POST['site'];
        $res = $db->sql("SELECT domain, theme FROM " . PREFIX . "sites WHERE site_id = " . $_SESSION['SITE_ID'], 1);
        $_SESSION['SITE_NAME'] = preg_replace('#^www\.|/$#', '', $res['DOMAIN']);
        $_SESSION['SITE_THEME'] = $res['THEME'];

        if ($_POST['ref']) header("Location: " . base64_decode($_POST['ref']));
        $_SESSION['CKFinder_UserRole'] = "admin";
        header("Location: index.php");
        exit;
    } elseif ($login && $password) {
        $res = $db->sql("SELECT * FROM " . PREFIX . "users WHERE login = '" . $login . "' AND pswrd = '" . $password . "' AND site_access_id = " . $_POST['site'], 1);
        if ($res['ID'] != '') {
            // посмотрим есть ли хотя бы одно вхождение в права на пу
            $r = $db->sql("SELECT count(*) cnt FROM " . PREFIX . "admin_access WHERE group_id = " . $res['GROUP_ID'], 1);
            if ($r['CNT'] > 0) {
                if ($res['STATUS'] == '0') {
                    $msg = "<br/><br/><font color=\"#FF0000\" style=\"font-weight: normal; font-size: 10px;\">" . $CPANEL_LANG['LOGIN_6'] . "!</font>";
                } else {
                    // занесем время вхождения
                    $db->sql("UPDATE " . PREFIX . "users SET dateonline = " . time() . ", ip_addr = '" . $_SERVER['REMOTE_ADDR'] . "' WHERE id = " . $res['ID']);
                    session_register("AdminPanel");
                    $_SESSION['user'] = $login;
                    $_SESSION['password'] = $password;
                    $_SESSION['ADMIN_ID'] = $res['ID'];
                    $_SESSION['ADMIN_GROUP_ID'] = $res['GROUP_ID'];
                    $_SESSION['AdminRoot'] = false;
                    if ($_POST['remember']) {
                        setcookie("WL_LOGIN_HASH", md5($password), (time() + 60 * 60 * 24 * 30));
                    } else {
                        setcookie("WL_LOGIN_HASH");
                    }
                    setcookie('mkse', $login, (time() + 60 * 60 * 24 * 30), '/');
                    setcookie('mkse_login', 1, 0, '/');
                    $_SESSION['SITE_ID'] = $_POST['site'];
                    $res = $db->sql("SELECT domain FROM " . PREFIX . "sites WHERE site_id = " . $_SESSION['SITE_ID'], 1);
                    $_SESSION['SITE_NAME'] = preg_replace('#^www\.|/$#', '', $res['DOMAIN']);
                    if ($_POST['ref']) header("Location: " . base64_decode($_POST['ref']));
                    $_SESSION['CKFinder_UserRole'] = "admin";
                    header("Location: index.php");
                    exit;
                }
            } else {
                $validationFailed = true;
            }
        } else {
            $validationFailed = true;
        }
    } else {
        $validationFailed = true;
    }
}
$tpl->assign('validationFailed', $validationFailed);
if ($_POST['login']) $log = $_POST['login'];
elseif ($_COOKIE['MKS_Login']) $log = base64_decode($_COOKIE['MKS_Login']); else $log = 'Administrator';
if ($_POST['pswrd']) $pas = $_POST['pswrd'];
elseif ($_COOKIE['MKS_Password']) $pas = base64_decode($_COOKIE['MKS_Password']);
if ($_COOKIE['MKS_Login'] && $_COOKIE['MKS_Password']) $_REQUEST['remember'] = 1;

$tpl->display('authorize');