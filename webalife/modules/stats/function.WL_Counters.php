<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_WL_Counters($params, &$smarty)
{
	global $mysql, $site;
	$counts = file($_SERVER['CMS_CORE'].'counters_'.$_SESSION['CMS_SITE_ID'].'.php');
	foreach($counts as $r)
	{
		$co = str_replace('\"','"',base64_decode($r));
		$co = str_replace("\'","'",$co);
		$co = str_replace('&','&amp;',$co);
		echo '<div align="center">'.$co.'</div>';
	}
}


?>