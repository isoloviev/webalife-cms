<?php
/*
Powered by MKS Engine (c) 2004-2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
Description: Engine
*/
error_reporting(E_ALL ^ E_NOTICE);

session_start();
session_name("DevLabCMS");

require_once('wl_config.php');

// проверим на наличие файла install.lock
if (!file_exists(CMS_ROOT_DIR . 'install.lock')) {
    echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div style="color: #FF0000;">Система не установлена!</div></body></html>';
    exit;
} else {

    // проверяем и запоминаем домен, с которого
    $_SESSION['CMS_HOST'] = preg_replace('#^www\.#', '', $_SERVER['HTTP_HOST']);
    require_once(CMS_CLASSES . "siter.class.php");
    $site = new siter();

    // Ищем пост данные SendForm
    if ($_POST['SendForm']) {
        if (!$_POST['FormCode']) $err[] = 'Введите защитный код!';
        if ($_POST['FormCode'] && base64_encode($_POST['FormCode']) != $_POST['FormOriCode']) $err[] = 'Защитный код введен неверно!';
        if (isset($_POST['message']) && strlen(trim($_POST['message'])) == 0) $err[] = 'Введите текст сообщения';
        if (is_array($err)) {
            define('FORM_ERROR', implode('<br/>', $err));
        } else {
            $fdata = $mysql->sql("SELECT * FROM " . PREFIX . "forms WHERE name_id = '" . $_POST['form_id'] . "'", 1);
            $res = $mysql->sql("SELECT max(oid) max FROM " . PREFIX . "forms_saved", 1);
            $oid = $res['MAX'] + 1;
            foreach ($_POST as $key => $value) {
                $el = $mysql->sql("SELECT id, text FROM " . PREFIX . "forms_elements WHERE name = '" . $key . "' AND form_id = " . $fdata['ID'], 1);
                if ($el['TEXT']) {
                    if (is_array($value)) {
                        $value = $value[0];
                    }
                    $mess[] = $el['TEXT'] . "\r\n---------------------------------------\r\n" . $value;
                    $mysql->sql("INSERT INTO " . PREFIX . "forms_saved SET oid = " . $oid . ", form_id = " . $fdata['ID'] . ", field_id = " . $el['ID'] . ", value = '" . $value . "', datetm = " . time() . ", site_id = " . $_SESSION['CMS_SITE_ID'] . ", lang = '" . SITELANG . "'");
                }
            }
            $message = "С сайта " . $_SERVER['HTTP_HOST'] . " был отправлен запрос на форму \"" . $fdata['NAME'] . "\":\r\n\r\n" . implode("\r\n\r\n", $mess) . "\r\n\r\n";
            $message .= "\r\n\r\n---------------------------------------\r\n\r\nДанное письмо отправлено роботом MKS Engine. Отвечать на него не нужно!";
            mail($fdata['EMAIL'], 'Запрос с сайта ' . $_SERVER['HTTP_HOST'], $message, "From: robot@" . $_SERVER['HTTP_HOST'] . "\r\nContent-Type: text/plain; charset=utf-8");
            header("Location: " . $_SERVER['CMS_ROOT_URL'] . HTMLLANG . $_REQUEST['url'] . "." . $_POST['form_id'] . ",sendok.html");
            exit;
        }
    }

    // Версия для печати статей
    if ($_REQUEST['print'] == 'article') {
        $site->_DetectURL($site->get_url());
        $cid = $_SESSION['REQUEST_PARAMS'][0];
        $aid = $_SESSION['REQUEST_PARAMS'][1];
        $res = $mysql->sql("SELECT a.*, cat.header_txt, cat.footer_txt FROM " . PREFIX . "docs a, " . PREFIX . "docs_cats cat WHERE a.cat_id = cat.id AND cat.site_id = " . $_SESSION['CMS_SITE_ID'] . " AND a.cat_id = " . $cid . " AND a.doc_id = " . $aid, 1);
        $content = $res['HEADER_TXT'] . $res['CONTENT'] . $res['FOOTER_TXT'];
        $site->assign(array('TITLE' => $res['TITLE'], 'CONTENT' => $content));
        $site->display('articles/print.tpl');
        exit;
    }

    // регистрация пользователя
    if ($_POST['RegIt']) {
        foreach ($_POST['USER'] as $key => $value) $$key = $value;
        if (!$NAME) $err[] = 'Введите ваше имя';

        if (!$EMAIL) $err[] = 'Введите e-mail, т.к. он является логином';
        if ($EMAIL && !$site->is_email($EMAIL)) $err[] = 'E-Mail указан не верно!';
        if ($EMAIL) {
            $rst = $mysql->sql("SELECT count(*) cnt FROM " . PREFIX . "users WHERE email = '" . $EMAIL . "'", 1);
            if ($rst['CNT'] != 0) $err[] = 'Такой e-mail уже имеется в системе';
        }
        if (!$PASS) $err[] = 'Введите пароль';
        if ($PASS && $PASS != $REPASS) $err[] = 'Введенные пароли не совпадают';
        if (is_array($err)) {
            $site->assign('PROFILE_ERROR', implode('<br/>', $err));
        } else {
            $sql[] = "name = '" . $NAME . "'";
            $sql[] = "email = '" . $EMAIL . "'";
            $sql[] = "login = '" . $EMAIL . "'";
            $sql[] = "pswrd = '" . md5($PASS) . "'";
            $mysql->sql("INSERT INTO " . PREFIX . "users SET " . implode(', ', $sql) . ", group_id = '3', datereg = " . time() . ", status = 1, ip_addr = '" . $_SERVER['REMOTE_ADDR'] . "'");
            $uid = $mysql->GetLastID();
            mail($_SESSION['CMS_ADMIN_EMAIL'], 'Регистрация на сайте', "Пользователь: " . $EMAIL . "\r\n\r\nПрошел регистрацию...\r\n\r\nАдминистрация", "From: info@" . $_SERVER['HTTP_HOST'] . "\r\nContent-Type: text/plain; charset=utf-8");
            mail($EMAIL, 'Регистрация на сайте ' . $_SERVER['HTTP_HOST'], "Поздравляем, Вы успешно прошли регистрацию. Ваш аккаунт будет включен после проверки Администрацией сайта.\r\n\r\nДанные для входа:\r\n\r\n\tПользователь: " . $EMAIL . "\r\n\tПароль:" . $site->my_str_replace($PASS) . "\r\n\r\n----\r\n\r\nС Уважением,\r\nАдминистрация сайта", "From: info@" . $_SERVER['HTTP_HOST'] . "\r\nContent-Type: text/plain; charset=utf-8");
            header("Location: " . $site->PrepareURL($site->get_url(), false) . ".html?a=ok");
            exit;
        }
    }

    // авторизация пользователя
    if ($_POST['UserLogon']) {
        if (!$_POST['UserLogin']) $err[] = 'Введите логин!';
        if (!$_POST['UserPassword']) $err[] = 'Введите пароль!';
        if (is_array($err)) {
            $site->assign('PROFILE_ERROR', implode('<br/>', $err));
        } else {
            $rst = $mysql->sql("SELECT * FROM " . PREFIX . "users WHERE login = '" . $site->my_str_replace($_POST['UserLogin']) . "' AND pswrd = '" . md5($_POST['UserPassword']) . "' AND status = 1", 1);
            if ($rst['ID'] != '') {
                $rr = $mysql->sql("SELECT SIGN_ID FROM ".PREFIX."rate WHERE user_id = ". $rst['ID'],1);
                if (!empty($rr['SIGN_ID'])) {
                    $rst['RATE_ID'] = $rr['SIGN_ID'];
                }
                $mysql->sql("UPDATE " . PREFIX . "users SET dateonline = " . time() . " WHERE id = " . $rst['ID']);
                $_SESSION['USER'] = $rst;
                $_SESSION['InUserSession'] = 'true';
                if (isset($_REQUEST['FromOrder'])) {
                    header("Location: " . $site->PrepareURL($site->get_url(), false) . ".order,2.html");
                } else {
                    header("Location: /index.html");
                }
                exit;
            } else {
                $site->assign('PROFILE_ERROR', 'Логин или пароль указаны не верно!');
            }
        }
    }

    // выход
    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['USER'], $_SESSION['InUserSession']);
        header("Location: /index.html");
        exit;
    }


    $site->main();
}