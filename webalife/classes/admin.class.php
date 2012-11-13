<?php
/**
 * DON'T MODIFY THIS FILE
 */

// require some files
require_once CMS_CLASSES . "siter.class.php";
require_once CMS_CLASSES . "mysql.class.php";
require_once CMS_CLASSES . "file.class.php";

// check for session
session_start();
if (isset($_SESSION['InAdminSession'])) {
    session_name("AdminPanel");
} else {
    $ref = base64_encode($_SERVER['REQUEST_URI']);
    header("Location: /webalife/admin/authorize.php?ref=" . $ref);
    exit;
}

if (!$_SESSION['AdminLang']) $_SESSION['AdminLang'] = DEFAULT_LANG;
if ($_REQUEST['CpanelLang']) $_SESSION['AdminLang'] = $_REQUEST['CpanelLang'];
define("SLANG", $_SESSION['AdminLang']);


class admin extends site
{
    var $ContentType = "";
    var $WorkSpaceTitle = "";

    function admin($TypeOfContent = "")
    {
        global $db, $SLANG;
        define('CMS_VERSION', $this->CMS_VERSION);
        $this->ContentType = $TypeOfContent;
        $db = new mysql();
        $db->connect();
        if(file_exists(CMS_MODULES.$_REQUEST['module'].'/admin/languages.php'))
        {
            require_once(CMS_MODULES.$_REQUEST['module'].'/admin/languages.php');
            $SLANG = $SLANG[SLANG];
        }
        $this->tpl = new template();
        // include lang
        include_once(CMS_LANGUAGES . 'language_admin_' . SLANG . '.php');
    }

    /**
     * @return template
     */
    function tpl()
    {
        return $this->tpl;
    }

    /* ??????????? ?????? ?????????? */
    function main($PageID = null)
    {
        global $db, $SLANG, $CPANEL_LANG;
        // ??????? ???
        $real = preg_replace('#/webalife/modules/#', '', $_SERVER['REQUEST_URI']);
        if (strpos($real, '?')) $_SESSION['CURI'] = substr($real, 0, strpos($real, '?'));
        else $_SESSION['CURI'] = $real;
        $this->tpl->assign('WorkSpaceTitle', $this->WorkSpaceTitle);
        $this->tpl->display('admin');
    }

    /* ?????? ??????????? */
    function handler()
    {
        if (class_exists("handler")) {
            $handler = new handler();
            if (method_exists($handler, "start")) {
                $handler->start();
            } else {
                echo 'Method Start() was not found in Handler Class!';
            }
        }
    }

    /*
    ?????????? ???? ? ??????? 00.00.0000
    @access - public
    */
    function date_parse($tmp_date)
    {
        $tmp1 = substr($tmp_date, 0, 4);
        $tmp2 = substr($tmp_date, 5, 2);
        $tmp3 = substr($tmp_date, 8, 2);
        return $tmp3 . "." . $tmp2 . "." . $tmp1;
    }

    /*
    ????????? ????????? ????
    @access - public
    */
    function htmlarea($field, $content, $height = 400, $toolbar = 'DevLab')
    {
        include_once(CMS_ROOT_DIR . "webalife/admin/ckeditor/ckeditor.php");
        $editor = new CKEditor();
        $editor->basePath = "/webalife/admin/ckeditor/";
        $editor->returnOutput = true;
        $editor->config['height'] = $height;
        $editor->config['toolbarSet'] = $toolbar;
        return $editor->editor($field, $content);
    }

    function ToolBar($items = array())
    {
        global $LANG;
        $this->tpl->getSmarty()->assign('buttons', $items);
        echo $this->tpl->getSmarty()->fetch('toolbar.tpl');
    }

    /* ???????? ?? ??????? ???? ???????	*/
    function access($only = '')
    {
        global $db;
        // ???? ?????????? ?????
        if ($_SESSION['AdminRoot']) return array("read" => true, "edit" => true, "kill" => true);
        // ???? ???? ????????????
        if ($_SESSION['demo']) return array("read" => true, "edit" => false, "kill" => false);
        $rst = $db->sql("SELECT * FROM " . PREFIX . "admin_access WHERE group_id = " . $_SESSION['ADMIN_GROUP_ID'] . " AND page_id = '" . $_SESSION['CURI'] . "'", 1);
        if ($rst['FREAD'] == 1) $ap1['read'] = true; else $ap1['read'] = false;
        if ($rst['FEDIT'] == 1) $ap1['edit'] = true; else $ap1['edit'] = false;
        if ($rst['FKILL'] == 1) $ap1['kill'] = true; else $ap1['kill'] = false;
        if ($only) return $ap1[$only];
        return $ap1;
    }

    /*
    Function of RSS Content
    */
    function BuildRSS($cont = array())
    {

        $content['rss;version="2.0"'] = array("channel" => $cont);
        // Link xml library
        require_once(CMS_CLASSES . "xml.class.php");
        $xml = new xml();
        $xml->Encode = "windows-1251";
        return $xml->getXMLdoc($content);
    }

    function PutSerialize($ar, $put)
    {
        $foo = unserialize($ar);
        foreach ($foo as $f) {
            if ($f == $put) continue;
            $n[] = $f;
        }
        $n[] = $put;
        return serialize($n);
    }

    /* Error builder */
    function ERR_INFO($string)
    {
        if(!is_array($string)) $err = $string;
        else $err = '<li>'.implode('</li><li>', $string).'</li>';
        $this->tpl->assign('message', $err);
        return $this->tpl->getSmarty()->fetch('alert-error.tpl');
    }

    /* Message builder */
    function MSG_INFO($string)
    {
        if(!is_array($string)) $err = $string;
        else $err = '<ul><li>'.implode('</li><li>', $string).'</li></ul>';
        $this->tpl->assign('message', $err);
        return $this->tpl->getSmarty()->fetch('alert-info.tpl');
    }
}
