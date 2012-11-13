<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * MKS Engine (c) 2006
 * Ivan S. Soloviev
 *
 * Модуль для отображения дополнительных модулей
 * @params - module="" - название модуля. если указано, выведет только этот модуль, вне зависимости от типа страницы
 */


function smarty_function_WL_Content($params, &$smarty)
{
    global $db, $site, $page_path, $page_type, $SITE, $cms_record_id;
    // выводим статический контент
    if($_GET['result'] == 'ConfirmOK') {
        echo '<p><font color="green">Ваш адрес электронной почты был успешно активирован!</font></p>';
    }

    if($_GET['result'] == "SubOK") {
        echo '<p><font color="green">Спасибо за подписку!<br/>На указанный адрес электронной почты было отправлено письмо с кодом активации!</font></p>';
    }

    if($_GET['result'] == "DeleteEmailOK") {
        echo '<p><font color="green">Ваш адрес электронной почты был успешно удален!</font></p>';
    }

    if(strlen(SUBSCRIBE_ERROR) > 15) {
        echo '<p><font color="green">'.SUBSCRIBE_ERROR.'</font></p>';
    }


    echo $SITE['Content'];

    // обработка динамического контента
    // смотрим на проверку модулей
    // фото галерея
    if(defined(cms_photo)) {
        $cats = $db->sql("SELECT oid FROM ".PREFIX."photo_objects WHERE page_id = ".$SITE['PAGE_ID']." ORDER BY sort_id", 2);
        $smarty->assign('PHOTO_GALLERY',$cats);
        $smarty->display("photo/gallery.tpl");
    }

    // если шаблон модуля имеется
    if(file_exists($site->template_dir.$page_type."/misc.tpl")) {
        // если все оки, запускаем модуль
        $smarty->display($page_type."/misc.tpl");
    }
    // смотрим на наличие быстрых ссылок
    $res = $db->sql("SELECT * FROM ".PREFIX."pages_links WHERE page_id = ".$SITE['PAGE_ID'], 2);
    if (sizeof($res) > 0) {
        foreach($res as $r) {
            if(!$print) {echo '<p>Читайте также:<ul>'; $print = true;}
            $p = $db->sql("SELECT p.page_path, c.menu_title FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_id = ".$r['LINK_ID'], 1);
            $PregPath = $site->PrepareURL($p['PAGE_PATH']);
            echo '<li><a href="'.$PregPath.'">'.$p['MENU_TITLE'].'</a></li>';
        }
    }
    if($print) echo '</ul></p>';

    // смотрим на наличие версии для печати
    if($SITE['PAGE_PRINT'] == 1) {
        $PregPath = $site->PrepareURL($SITE['PAGE_PATH'], false).'.PrintMode.html';
        echo '<p><img src="'.$_SERVER['CMS_ROOT_URL'].'images/mks/print.gif" alt="" border="0" style="position: relative; top: 5px;">&nbsp;<a href="'.$PregPath.'" target="_blank">версия для печати</a></p>';
    }
}

?>
