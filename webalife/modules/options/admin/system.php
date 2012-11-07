<?php
/*
Powered by MKS Engine (c) 2005
Created: Ivan S. Soloviev, webmaster@mk-studio.ru

Просмотр информации о сервере
*/

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function Start()
	{
		global $db, $admin, $SLANG;
		$opt = ini_get_all();
		echo '<table class="table table-striped table-bordered">';
        echo '<tr><td colspan="3" align="center"><b>Информация о PHP</b></td></tr>';
		echo '<tr bgColor="#FFFFFF"><td width="60%">Версия PHP</td><td align="center" width="20%">'.phpversion().'</td><td align="center" width="20%" style="font-weight: bold;">'.(version_compare("4.0.0", phpversion(), "<=") ? '<font color="green">OK</font>' : '<font color="red">Need 4.0.0 or older</font>').'</td></tr>';
		echo '<tr bgColor="#FFFFFF"><td>Безопасный режим</td><td align="center">'.($opt['safe_mode']['local_value'] == '1' ? 'ON' : 'OFF').'</td><td align="center" style="font-weight: bold;">'.($opt['file_uploads']['local_value'] == '1' ? '<font color="green">OK</font>' : '<font color="red">Need to bee ON</font>').'</td></tr>';	
		echo '<tr bgColor="#FFFFFF"><td>Магические кавычки</td><td align="center">'.($opt['magic_quotes_gpc']['local_value'] == '1' ? 'ON' : 'OFF').'</td><td align="center" style="font-weight: bold;"><font color="green">OK</font></td></tr>';	
		echo '<tr bgColor="#FFFFFF"><td>Загрузка файлов на сервер</td><td align="center">'.($opt['file_uploads']['local_value'] == '1' ? 'ON' : 'OFF').'</td><td align="center" style="font-weight: bold;">'.($opt['file_uploads']['local_value'] == '1' ? '<font color="green">OK</font>' : '<font color="red">Need to bee ON</font>').'</td></tr>';	
		echo '<tr bgColor="#FFFFFF"><td>Максимальный размер загружаемого файла</td><td align="center">'.$opt['post_max_size']['local_value'].'</td><td align="center" style="font-weight: bold;">'.($opt['post_max_size']['local_value'] != '' ? '<font color="green">OK</font>' : '<font color="red">Need to bee set</font>').'</td></tr>';	
		echo '</table>';		
		echo '<table class="table table-striped table-bordered">';
		echo '<tr><td colspan="3" align="center"><b>Информация о MySQL</b></td></tr>';
		echo '<tr bgColor="#FFFFFF"><td width="60%">Версия</td><td align="center" width="20%">'.mysql_get_server_info().'</td><td align="center" width="20%" style="font-weight: bold;">'.(version_compare("3.23", mysql_get_server_info(), "<=") ? '<font color="green">OK</font>' : '<font color="red">Need 4.0.0 or older</font>').'</td></tr>';
		echo '<tr bgColor="#FFFFFF"><td>Кодировка соединения</td><td align="center">'.mysql_client_encoding().'</td><td align="center">&mdash;</td></tr>';	
		$res = $db->sql("SHOW TABLE STATUS FROM `".DB_NAME."` ", 2);
		foreach($res as $r) {
			$size += $r['DATA_LENGTH'];
		}
		echo '<tr bgColor="#FFFFFF"><td>Текущий размер базы данных</td><td align="center">'.round($size/1024,2).' KB</td><td align="center">&mdash;</td></tr>';	
		echo '</table>';
		echo '<table class="table table-striped table-bordered">';
		echo '<tr><td colspan="3" align="center"><b>Информация об установленных модулях '.CMS_VERSION.'</b></td></tr>';
		$res = $db->sql("SELECT * FROM ".PREFIX."modules ORDER BY binary(name)", 2);
		foreach($res as $r) {
			echo '<tr bgColor="#FFFFFF"><td width="60%">'.$r['NAME'].'</td><td align="center" width="20%">'.$r['VERSION'].'</td><td align="center">&mdash;</td></tr>';	
		}
		echo '</table>';
	}
	
}

global $SLANG;
$admin=new admin('SystemManager');
$admin->WorkSpaceTitle = $SLANG['Page Header System'];

$admin->main();
