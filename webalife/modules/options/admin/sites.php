<?php
/*
Powered by MKS Engine (c) 2005
Created: Ivan S. Soloviev, webmaster@mk-studio.ru

Настройка сайтов, которые обрабатываются системой
*/

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function Start()
	{
		global $db, $admin, $SLANG;
		if($_SESSION['ADMIN_ID'] != -1) {
			echo $admin->msg_info($SLANG['access denied']);
			return;
		}
		if(is_numeric($_REQUEST['site'])) return $this->SiteManager($_REQUEST['site']);
		$items[] = array('title'=>$SLANG['add site'],'path'=>'?site=0');
		$admin->ToolBar($items);

		$sites = $db->sql("SELECT site_id id, name, domain FROM ".PREFIX."sites ORDER BY `default` DESC, SITE_ID ASC", 2);
        $admin->tpl()->assign(array(
            'header' => array(
                array('#', '5%'),
                'Название',
                array('Домен', '20%')
            ),
            'rows' => $sites,
            'reason_to_delete' => 'banner',
            'title_id' => "banner",
            'do_not_sort' => true,
            'delete_prohibited' => true
        ));
        echo $admin->tpl()->fetch('table');
	}
	
	/* Редактирование сайта */
	function SiteManager($site)
	{
		global $db, $SLANG, $admin;
		if($site > 0) {
			$data = $db->sql("SELECT * FROM ".PREFIX."sites WHERE site_id = ".$site, 1);
		} 
		if (sizeof($_POST['SITE']))
            foreach($_POST['SITE'] as $key=>$value) $data[$key] = $value;
		if(strlen(ERROR) > 5) echo ERROR;
		if($_REQUEST['result'] == 'upOK') echo $admin->msg_info($SLANG['result 1']);
		echo '<p><a class="btn" href="sites.php">'.$SLANG['back to'].'</a></p>';
		echo '<form action="?site='.$site.'" method="post"><table cellspacing="0" cellpadding="3" border="0" style="width: 100%;" class="Manage">';
		echo '<tr><td class="RightTD" style="width: 20%;">'.$SLANG['table title 4'].'<font color="red">*</font>:</td><td><input type="text" name="SITE[NAME]" value="'.$data['NAME'].'" class="field" style="width: 100%;" maxlength="100"></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['table title 5'].'<font color="red">*</font>:</td><td><input type="text" name="SITE[DOMAIN]" value="'.$data['DOMAIN'].'" class="field" style="width: 100%;" maxlength="200"></td></tr>';
		echo '<tr><td class="RightTD"></td><td><font class="hint">'.$SLANG['hint 2'].'</font></td></tr>';
		echo '<tr><td class="RightTD"></td><td><label class="checkbox"><input type="checkbox" name="SITE[DEFAULT]" value="1" '.($data['DEFAULT'] == 1 ? 'checked' : '').'> '.$SLANG['table title 7'].'</label></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['table title 6'].':</td><td><textarea name="SITE[ALIASES]" rows="5" cols="10" class="field" style="width: 100%; height: auto; background-image: none;">'.$data['ALIASES'].'</textarea></td></tr>';
		echo '<tr><td class="RightTD"></td><td><font class="hint">'.$SLANG['hint 1'].' '.$SLANG['hint 2'].'</font></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['table title 8'].':</td><td><input type="text" name="SITE[NAME_RU]" value="'.$data['NAME_RU'].'" class="field" style="width: 100%;" maxlength="255"></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['table title 10'].':</td><td><input type="text" name="SITE[ADMIN_EMAIL]" value="'.$data['ADMIN_EMAIL'].'" class="field" style="width: 100%;" maxlength="255"></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['table title 11'].':</td><td><select name="SITE[THEME]" size="1" class="field" style="width: 100%;">'.$this->ListThemes($data['THEME']).'</select></td></tr>';
		echo '<tr><td class="RightTD"></td><td><input type="submit" name="SaveIt" value="'.$SLANG['btn save'].'" class="btn btn-primary"></td></tr>';
		echo '</table></form>';
	}
	
	function ListThemes($current)
	{
		$handle=opendir(CMS_ROOT_DIR."webalife/templates/");
		while (false !== ($file = readdir($handle))) {
				if($file=='.' || $file=='..') continue;
				if(!preg_match('#^tpl_|\.htaccess|globals#', $file)) {
					$tpl[] = $file;
				}
		}
		closedir($handle);
		sort($tpl);
	    $tmp = "";
		foreach($tpl as $file) {
			if($current == $file) $sel = "selected"; else $sel = "";
			$tmp.="<option value=\"".$file."\" ".$sel.">".$file."</option>";
		}
		return $tmp;
	}
}

global $SLANG;
$admin=new admin('SitesManager');
$admin->WorkSpaceTitle = $SLANG['Page Header Sites'];

if($_POST['SaveIt']) {
	foreach($_POST['SITE'] as $key=>$value) $$key = $value;
	if(!$NAME) $err[] = $SLANG['error 1'];
	if(!$DOMAIN) $err[] = $SLANG['error 2'];
	if($DOMAIN && !eregi('([\.a-z0-9\-]+)\.([a-z]{1,4})|([\.a-z0-9\-]+)',$DOMAIN)) $err[] = $SLANG['error 3'];
	if(!$DEFAULT) {
		$res = $db->sql("SELECT count(*) cnt FROM ".PREFIX."sites WHERE `default` = 1",1);
		if($res['CNT'] == 0) $err[] = $SLANG['error 4'];
	}
	if(is_array($err)) {
		define("ERROR",$admin->err_info('<li>'.implode('<li>',$err)));
	} else {
		$sql[] = "name = '".$admin->my_str_replace($NAME)."'";
		$sql[] = "domain = '".$DOMAIN."'";
		$sql[] = "aliases = '".$ALIASES."'";
		$sql[] = "name_ru = '".$admin->my_str_replace($NAME_RU)."'";
		$sql[] = "admin_email = '".$admin->my_str_replace($ADMIN_EMAIL)."'";
		$sql[] = "copyright_ru = '".$admin->my_str_replace($COPYRIGHT_RU)."'";				
		$sql[] = "theme = '".$THEME."'";
		if($DEFAULT == 1) {
			$sql[] = "`default` = 1"; 
			$db->sql("UPDATE ".PREFIX."sites SET `default` = 0 WHERE site_id != ".$_REQUEST['site']);
		} else $sql[] = "`default` = 0";
		if($_REQUEST['site'] == 0) {
			$db->sql("INSERT INTO ".PREFIX."sites SET ".implode(', ',$sql));
			$_REQUEST['site'] = $db->GetLastID();
			// вставляем страницу
			$db->sql("INSERT INTO ".PREFIX."pages SET site_id = ".$_REQUEST['site'].", page_order = 1, page_parent = 0, page_path = '/', page_tmplt = 'default'");
			$f = $db->GetLastID();
			$db->sql("INSERT INTO ".PREFIX."pages_content SET page_id = ".$f.", lang = '".$GLOBALS['DEFAULT_LANG']."', menu_title = 'Simple Page', page_title = 'Simple Title', header_title = 'Simple Header Title', page_content = 'This page was generate by MKS Engine'");
		} else {
			$db->sql("UPDATE ".PREFIX."sites SET ".implode(', ',$sql)." WHERE site_id = ".$_REQUEST['site']);
		}
		header("Location: ?site=".$_REQUEST['site']."&result=upOK");
		exit;
	}
}

$admin->main();
