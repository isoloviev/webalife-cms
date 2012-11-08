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
 * Модуль для отображения баннеров на сайте
 */


function smarty_block_MKS_Banners($params, $content, &$smarty)
{
	global $mysql, $site, $cms_record_id;
	if(!ereg('left|right|top|bottom|center',$params['align'])) return '';
	$banners = $mysql->sql("SELECT * FROM ".PREFIX."banners WHERE align = '".$params['align']."' AND active = 0 AND lang = '".SITELANG."' AND site_id = ".$_SESSION['CMS_SITE_ID']." ORDER BY sort_id DESC",2);
	$i=0;
	foreach($banners as $b) {
		// смотрим можно ли показывать данный баннер на данной странице
		$p = $mysql->sql("SELECT count(*) cnt FROM ".PREFIX."banners_pages WHERE banner_id = ".$b['ID']." AND page_id = ".$cms_record_id, 1);
		if($p['CNT'] == 1) continue;
		$html = $content;
		if($b['XCODE'] != '')		$html = str_replace('[BANNER]',$b['XCODE'],$html);
		elseif($b['EXT'] != 'gif') 	$html = str_replace('[BANNER]', ($b['URL'] != '' ? '<a href="'.$b['URL'].'" title="'.$b['TITLE'].'" target="'.$b['TARGET'].'">' : '').'<img src="/thumbnails.php?picname=files/Banners/'.$b['ID'].'.'.$b['EXT'].($b['XWIDTH'] ? '&w='.$b['XWIDTH'] : '').($b['XHEIGHT'] ? '&h='.$b['XHEIGHT'] : '').'" alt="'.$b['TITLE'].'" border="0" '.($i < (count($banners) - 1) ? 'style="margin-right: 20px;"' : '').'>'.($b['URL'] != '' ? '</a>' : ''), $html);
		else						$html = str_replace('[BANNER]', ($b['URL'] != '' ? '<a href="'.$b['URL'].'" title="'.$b['TITLE'].'" target="'.$b['TARGET'].'">' : '').'<img src="/files/Banners/'.$b['ID'].'.'.$b['EXT'].'" alt="'.$b['TITLE'].'" border="0" '.($b['XWIDTH'] ? 'width="'.$b['XWIDTH'].'"' : '').' '.($b['XHEIGHT'] ? 'height="'.$b['XHEIGHT'].'"' : '').' '.($i < (count($banners) - 1) ? 'style="margin-right: 20px;"' : '').'>'.($b['URL'] != '' ? '</a>' : ''), $html);
		if(InAdminSession === true) {
			//$html .= '<div align="center" style="border: 1px solid #ededed; padding: 3px; background-color: #f2f2f2; color: black;">'.($i>0 ? '<a href="'.$_SERVER['CMS_ROOT_URL'].'~engine/'.$_SERVER['CMS_PLUGINS'].'banners/admin.php?bid='.$b['ID'].'&sort=up&refurl='.CURRENT_PATH.'">вверх</a> | ' : '').($i<count($banners)-1 ? '<a href="'.$_SERVER['CMS_ROOT_URL'].'~engine/'.$_SERVER['CMS_PLUGINS'].'banners/admin.php?bid='.$b['ID'].'&sort=down&refurl='.CURRENT_PATH.'">вниз</a> | ' : '').'<a href="'.$_SERVER['CMS_ROOT_URL'].'~engine/'.$_SERVER['CMS_PLUGINS'].'banners/admin.php?banner='.$b['ID'].'&InLang='.SITELANG.'&refurl='.CURRENT_PATH.'">изменить</a> | <a href="'.$_SERVER['CMS_ROOT_URL'].'~engine/'.$_SERVER['CMS_PLUGINS'].'banners/admin.php?delbanner='.$b['ID'].'&InLang='.SITELANG.'&refurl='.CURRENT_PATH.'" onClick="return confirm(\'Вы действительно хотите удалить данный баннер?\');">удалить</a></div>';
		}
		$d[] = $html;
		$i++;
	}
	if(InAdminSession === true) {
		//$d[] = '<div align="center" style="padding: 3px; margin-top: 10px;"><a href="'.$_SERVER['CMS_MODULES_URL'].'banners/admin/index.php?banner=0&align='.$params['align'].'&InLang='.SITELANG.'&refurl='.CURRENT_PATH.'">добавить баннер</a></div>';
	}
	$_output = implode("\r\n", $d);
	return $assign ? $smarty->assign($assign, $_output) : $_output;
}


?>