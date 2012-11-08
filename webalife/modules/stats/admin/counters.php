<?php
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
*/

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function start()
	{
		global $db, $admin, $CPANEL_LANG;
		if(!$admin->access('read')) {
			echo $admin->msg_info($CPANEL_LANG['access denied']);
			return;
		}
		$items[] = array('title'=>'Добавить счетчик','path'=>'?counter=add');
		$admin->ToolBar($items);
        if($_REQUEST['result'] == "upOK") echo $admin->MSG_INFO("Данные обновлены");
		echo '<form action="" method="post">';
		echo '<table cellspacing="0" cellpadding="3" class="Manage">';
		$counts = $db->sql("SELECT * FROM ".PREFIX."counters ORDER BY counter_id", 2);
		if($_GET['counter'] == 'add' || sizeof($counts) == 0) $counts[] = '';
		foreach($counts as $r) {
			$co = str_replace('\"','"',base64_decode($r['COUNTER_CODE']));
			$co = str_replace("\'","'",$co);
			echo '<tr><td class="RightTD" style="width: 20%;">Счетчик:</td><td><input type="hidden" name="counterid[]" value="'.$r['COUNTER_ID'].'"><textarea name="counter[]" rows=10 cols=20 class="field" style="width: 100%; height: auto; background-image: none;">'.$co.'</textarea></td></tr>';
		}
		echo '<tr><td class="RightTD" style="width: 20%;"></td><td><input type="submit" name="SaveIt" value="Сохранить изменения" class="btn btn-primary" '.($admin->access('edit') ? '' : 'disabled').'></td></tr>';
		echo '</table></form>';
	}
}

global $db;
$admin=new admin('Counters');
$admin->WorkSpaceTitle = 'Счетчики посещаемости';
if($_POST['SaveIt'])
{
    $i = 0;
	foreach($_POST['counter'] as $r) {
		if(strlen($r) < 10) continue;
		$db->sql("REPLACE INTO ".PREFIX."counters SET counter_id = ".intval($_POST['counterid'][$i]).", counter_code = '".base64_encode($r)."'");
        $i++;
	}
	header("Location: counters.php?result=upOK");
}
$admin->main();
