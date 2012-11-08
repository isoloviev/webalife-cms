<?php
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, webmaster@mk-studio.ru
*/

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function start()
	{
		global $db, $admin, $CPANEL_LANG, $SUPPORT_LANG;
		if(!$admin->access('read')) {
			echo $admin->msg_info($CPANEL_LANG['access denied']);
			return;
		}
		$align=array('left'=>'Левая зона', 'right'=>'Правая зона', 'top'=>'Верхняя зона', 'bottom'=>'Нижняя зона', 'center'=>'Центральная зона');
		if(is_numeric($_REQUEST['banner'])) return $this->BannerModify($_REQUEST['banner']);
		if($_REQUEST['result'] == "killOK") echo $admin->MSG_INFO("<li>Баннер успешно удален!</li>");
		$items[] = array('title'=>'Добавить баннер','path'=>'?banner=0');
		$admin->ToolBar($items);

        $banners = $db->sql("SELECT id, title, align, concat(xwidth, 'x', xheight) size FROM ".PREFIX."banners WHERE lang = '".$_SESSION['AdminLang']."' AND site_id = ".$_SESSION['SITE_ID']." ORDER BY align ASC, sort_id DESC", 2);

        $admin->tpl()->assign(array(
            'header' => array(
                array('#', '5%'),
                'Название',
                array('Позиционирование', '20%'),
                array('Размер', '15%')
            ),
            'rows' => $banners,
            'reason_to_delete' => 'banner',
            'title_id' => "banner",
        ));
        echo $admin->tpl()->fetch('table');
	}
	
	function BannerModify($banner)
	{
		global $db, $admin, $banner_id;
		$banner_id = $banner;
		if($_REQUEST['result'] == "upOK") echo $admin->MSG_INFO("<li>Информация успешно обновлена!</li>");
		if($banner > 0) {
			$data = $db->sql("SELECT * FROM ".PREFIX."banners WHERE id = ".$banner, 1);			
		}
        if (sizeof($_POST['DAT']))
		    foreach($_POST['DAT'] as $key=>$value) $data[$key] = $value;
		if($data['ALIGN'] == '') $data['ALIGN'] = $_REQUEST['align'];
		if(strlen(ERRINFO) > 15) echo $admin->err_info(ERRINFO);
		echo '<p><a class="btn" href="index.php">к списку баннеров</a></p>';
		echo '<form name="MainForm" action="?banner='.$banner.'" method="post" enctype="multipart/form-data"><table cellspacing="0" cellpadding="3" border="0" class="Manage" style="width: 100%;">';
		echo '<tr><td class="RightTD" width="20%">Название<font color="red">*</font>:</td><td><input type="text" name="DAT[TITLE]" value="'.$data['TITLE'].'" class="field" style="width: 100%;" maxlength="100"></td></tr>';
		echo '<tr><td class="RightTD">URL:</td><td><input type="text" name="DAT[URL]" value="'.$data['URL'].'" class="field" style="width: 100%;" maxlength="255"></td></tr>';
		echo '<tr><td class="RightTD">Путь к баннеру<font color="red">*</font>:<br/><font color="gray">jpeg,gif,png,swf</font></td><td><input type="file" name="banner" value="" class="field" style="width: 100%;"></td></tr>';
		if($data['FILE_ID'] > 0) {
            $file = CFile::getImage($data['FILE_ID'], $data['XWIDTH'], $data['XHEIGHT']);
			echo '<tr><td class="RightTD">Баннер:</td><td align="left">';
			if($data['XCODE'] != '') echo $data['XCODE'];
			elseif($data['EXT'] != 'gif') echo ($data['URL'] != '' ? '<a href="'.$data['URL'].'" title="'.$data['TITLE'].'" target="_blank">' : '').'<img src="'.$file['SRC'].'" alt="'.$data['TITLE'].'" border="0">'.($data['URL'] != '' ? '</a>' : '');
			else echo ($data['URL'] != '' ? '<a href="'.$data['URL'].'" title="'.$data['TITLE'].'" target="_blank">' : '').'<img src="'.$file['SRC'].'" alt="'.$data['TITLE'].'" border="0">'.($data['URL'] != '' ? '</a>' : '');
			echo '</td>';
		}
		echo '<tr><td class="RightTD">Метод перехода:</td><td><select name="DAT[TARGET]" size="1" class="field" style="width: 150px;">'.$admin->select_from_array(array('_blank'=>'Новое окно','_self'=>'Текущее окно'), $data['TARGET']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Позиционирование:</td><td><select name="DAT[ALIGN]" size="1" class="field" style="width: 150px;">'.$admin->select_from_array(array('left'=>'Левая часть сайта','right'=>'Правая часть сайта', 'top'=>'Верхняя часть сайта', 'bottom'=>'Нижняя часть сайта', 'center'=>'Центральная часть сайта'), $data['ALIGN']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Статус:</td><td><select name="DAT[ACTIVE]" size="1" class="field" style="width: 150px;">'.$admin->select_from_array(array('активен','заблокирован'), $data['ACTIVE']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Размер баннера:</td><td><table cellspacing="0" cellpadding="3">';
		echo '<tr><td style="padding-left: 0px;">Высота, px:</td><td><input type="text" name="DAT[XHEIGHT]" value="'.$data['XHEIGHT'].'" class="field" style="width: 75px;" maxlength="5"></td></tr>';
		echo '<tr><td style="padding-left: 0px;">Ширина, px:</td><td><input type="text" name="DAT[XWIDTH]" value="'.$data['XWIDTH'].'" class="field" style="width: 75px;" maxlength="5"></td></tr>';
		echo '</table></td></tr>';
		echo '<tr><td class="RightTD"></td><td style="color: gray;">если хотите использовать размеры по умолчанию, оставьте поля пустыми</td></tr>';
		echo '<tr><td class="RightTD">Показывать на страницах:</td><td><table cellspacing="1" cellpadding="3" bgColor="#D9D9D9" width="100%"><tr style="background-color: #f0f0f0; font-weight: bold; height: 30px;"><td align="center" width="80%">Название страницы</td><td align="center">Показывать</td></tr>';
		$this->AllPages();
		echo '<tr style="background-color: white;"><td></td><td align="center"><a href="javascript: void(0);" onClick="javascript: PagesAll(document.MainForm);">Выбрать все</a></td></tr>';
		echo '</table></td></tr>';
		echo '<tr><td class="RightTD"></td><td><input type="hidden" name="refurl" value="'.$_REQUEST['refurl'].'"/><input type="hidden" name="SaveThis" value="true"/><input type="submit" value="Сохранить" class="btn btn-primary" '.(!$admin->access('edit') ? 'disabled' : '').'/></td></tr>';
		echo '<tr><td class="RightTD"></td><td style="color: gray;">Необходимый код для вставки баннера будет сгенерирован автоматически</td></tr>';
		echo '</table></form>';
	}

	/* Список доступных страниц */
	function AllPages($parent = 0, $cnt = 0)
	{
		global $db, $cnt, $banner_id, $q;
		if(!isset($q)) $q = -1;
		$pages=$db->sql("SELECT p.page_id, p.page_parent, c.record_id rid, c.menu_title, p.page_path, p.page_order FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_parent = ".$parent." AND c.lang = '".$_SESSION['AdminLang']."' AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_order", 2);
		for($i=0;$i<count($pages);$i++) 
		{
			$q++;
			$p = $db->sql("SELECT count(*) cnt FROM ".PREFIX."banners_pages WHERE banner_id = ".$banner_id." AND page_id = ".$pages[$i]['RID'], 1);
			echo '<tr style="background-color: white;"><td>'.str_repeat("&nbsp;",($cnt*5)).$pages[$i]['MENU_TITLE'].'</td><td align="center"><input type="hidden" name="ALLP['.$q.']" value="'.$pages[$i]['RID'].'"><input type="checkbox" name="SHOWP['.$q.']" value="'.$pages[$i]['RID'].'" '.($p['CNT'] == 0 ? 'checked' : '').'></td></tr>';			
			$cnt++;
			$this->AllPages($pages[$i]['PAGE_ID'], $cnt);
			$cnt--;			
		}		
	}
	
}

global $SLANG;
$admin=new admin('BannerManager');
$admin->WorkSpaceTitle = 'Баннерная система';

/* Сохранение данных страницы */
if($_POST['SaveThis'] == "true") {
	if($admin->access('edit')) {
		foreach($_POST['DAT'] as $key=>$value) $$key = $value;
		if(!$TITLE) $err[] = 'Введите название для баннера!';
		if($_REQUEST['banner'] == 0 && $_FILES['banner']['name'] == '') $err[] = 'Укажите баннер!';
		if($_FILES['banner']['name'] && !ereg('jpeg|gif|png|flash',$_FILES['banner']['type'])) $err[] = 'Файл должен быть в формате JPEG, PNG, GIF или SWF!';
		if(is_array($err)) {
			define('ERRINFO','<li>'.implode('<li>',$err));
		} else {
			if($_REQUEST['banner'] == 0) {
				$db->sql("INSERT INTO ".PREFIX."banners SET site_id = ".$_SESSION['SITE_ID'].", lang = '".$_SESSION['AdminLang']."', title = '".$admin->my_str_replace($TITLE)."', align = '".$ALIGN."', active = '".$ACTIVE."', url = '".$URL."', target='".$TARGET."'");
				$_REQUEST['banner'] = $db->GetLastID();
				// порядковый номер в зависимости от позиционирования
				$res = $db->sql("SELECT max(sort_id) stid FROM ".PREFIX."banners WHERE site_id = ".$_SESSION['SITE_ID']." AND lang = '".$_SESSION['AdminLang']."'", 1);
				$db->sql("UPDATE ".PREFIX."banners SET sort_id = ".($res['STID'] + 1)." WHERE id = ".$_REQUEST['banner']);
			} else {
				$db->sql("UPDATE ".PREFIX."banners SET title = '".$admin->my_str_replace($TITLE)."', align = '".$ALIGN."', active = '".$ACTIVE."', url = '".$URL."', sort_id = '".$SORT_ID."', target='".$TARGET."' WHERE id = ".$_REQUEST['banner']);
			}
            if($_FILES['banner']['name']) {
                $size = getimagesize($_FILES['banner']['tmp_name']);
                if(!$XHEIGHT) $XHEIGHT = $size[0];
                if(!$XWIDTH) $XWIDTH = $size[1];
                $file = CFile::upload($_FILES['banner']);
                // resize image
                $file = CFile::resizeImage($file, $XWIDTH, $XHEIGHT);
                $db->sql("UPDATE ".PREFIX."banners SET xwidth=".$file['WIDTH'].", xheight=".$file['HEIGHT'].", xcode='".$code."', ext = '".$file['EXT']."', file_id = ".$file['ID']." WHERE id = ".$_REQUEST['banner']);
			} else {
				$db->sql("UPDATE ".PREFIX."banners SET xwidth=".$XWIDTH.", xheight=".$XHEIGHT." WHERE id = ".$_REQUEST['banner']);
			}
			// сохраняем инфу о страницах
			$db->sql("DELETE FROM ".PREFIX."banners_pages WHERE banner_id = ".$_REQUEST['banner']);
			for($i=0;$i<count($_POST['ALLP']);$i++) {
				if($_POST['ALLP'][$i] != $_POST['SHOWP'][$i]) $db->sql("INSERT INTO ".PREFIX."banners_pages SET banner_id = ".$_REQUEST['banner'].", page_id = ".$_POST['ALLP'][$i]);
			}
			if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
			else header("Location: index.php?banner=".$_REQUEST['banner']."&result=upOK");
			exit;
		}	
	}
}

/* Удаление баннера */
if(is_numeric($_REQUEST['delete'])) {
	if($admin->access('kill')) {
		$f = $db->sql("SELECT file_id FROM ".PREFIX."banners WHERE id = ".$_REQUEST['delete'], 1);
        $db->sql("DELETE FROM ".PREFIX."banners WHERE id = ".$_REQUEST['delete']);
		$db->sql("DELETE FROM ".PREFIX."banners_pages WHERE banner_id = ".$_REQUEST['delete']);
		if ($f['FILE_ID'] > 0) {
            CFile::delete($f['FILE_ID']);
        }
		header("Location: index.php?result=killOK");
		exit;
	}
}

/* Позиция вниз */
if(is_numeric($_REQUEST['banner']) && $_REQUEST['sort'] == 'down') {
	if($admin->access('edit')) {
		$db->sql("UPDATE ".PREFIX."banners SET sort_id = (sort_id - 1) WHERE id = ".$_REQUEST['banner']);
		if($_REQUEST['refurl']) {header("Location: ".$_REQUEST['refurl']);exit;}
	}
}


/* Позиция вверх */
if(is_numeric($_REQUEST['banner']) && $_REQUEST['sort'] == 'up') {
	if($admin->access('edit')) {
		$db->sql("UPDATE ".PREFIX."banners SET sort_id = (sort_id + 1) WHERE id = ".$_REQUEST['banner']);
		if($_REQUEST['refurl']) {header("Location: ".$_REQUEST['refurl']);exit;}
	}
}
$admin->main();
