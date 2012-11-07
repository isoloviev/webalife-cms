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
 * ћодуль дл€ отображени€ новостей
 * @params - feed_id = "" - об€зательный параметр (идентификатор ленты новостей)
 */


function smarty_function_MKS_Module_News($params, &$smarty)
{
	global $mysql, $site, $SLANG, $cms_record_id, $NewsView;
	// include language file
	$NewsView = false;
	require_once("admin/languages.php");
	if(is_array($SLANG[SITELANG])) $SLANG = $SLANG[SITELANG];
	
	switch($params['mode']) {
		case "feed":
			smarty_function_MKS_Module_News_Feeds_ByID($params['feed_id'], $smarty);
			break;
		case "news":
			smarty_function_MKS_Module_News_Feeds_ByNews($params['feed_id'], $params['news_id'], $smarty);
			break;
		case "anounce":
			smarty_function_MKS_Module_News_Feeds_ByShow($params['align'], $smarty);
			break;
		default:
			smarty_function_MKS_Module_News_Feeds($smarty);
			break;
	}
}

function smarty_function_MKS_Module_News_Feeds(&$smarty)
{
	global $mysql;
	$rest = $mysql->sql("SELECT * FROM ".PREFIX."news_feeds WHERE site_id = ".$_SESSION['CMS_SITE_ID']." AND lang = '".SITELANG."' ORDER BY sort_id", 2);
	$res = array();
	foreach($rest as $r) {
		if(strpos($r['ABOUT'], '<hr') > 0) {
			$r['ABOUT'] = substr($r['ABOUT'], 0, strpos($r['ABOUT'], '<hr'));
			$r['ABOUT'] = str_replace('<p>&nbsp;</p>', '', $r['ABOUT']);
		}
		$res[] = $r;
	}
	$smarty->assign('Feeds', $res);
}

function smarty_function_MKS_Module_News_Feeds_ByID($feed_id, &$smarty) 
{
	global $mysql, $site;
	$feed_id = intval($feed_id);
	if($feed_id > 0) {
		$feed = $mysql->sql("SELECT * FROM ".PREFIX."news_feeds WHERE id = ".$feed_id." AND site_id = ".$_SESSION['CMS_SITE_ID']." AND lang = '".SITELANG."'", 1);
		if(!is_numeric($feed['ID'])) return;
	}
	$items = $mysql->sql("SELECT n.news_id id, n.short_title Title, n.short_content Text, n.datetm, n.feed_id, n.image FROM ".PREFIX."news n, ".PREFIX."news_feeds f WHERE f.id = n.feed_id AND f.lang = '".SITELANG."' AND f.site_id = ".$_SESSION['CMS_SITE_ID']." AND f.id = ".$feed_id." ORDER BY n.".$feed['SORTBY']." DESC", 2);
	foreach($items as $nit) {
		$nit['DATE'] = $site->format_date('rM', $nit['DATETM']);
		$imgs = unserialize($nit['IMAGE']);
		$nit['IMAGE'] = $imgs[0];
		$NewsItems[] = array("Date"=>$nit['DATE'], "Title"=>$nit['TITLE'], "Text"=>$nit['TEXT'], "Image"=>$nit['IMAGE'], "ID"=>$nit['ID'], "FEED_ID"=>$nit['FEED_ID']);
	}
	// calculate pages
	if($_REQUEST['pg']) { $page=$_REQUEST['pg']; } else { $page=1; }
	$allcnt=$recs=count($NewsItems); $max=$page*$feed['PERPAGE']; $min=$max-$feed['PERPAGE'];
	if($max > $recs) $max=$recs;
	// bindings
	$smarty->assign(array('min'=>$min, 'max'=>$max));	
	// bind news
	$smarty->assign("Feeds", $NewsItems);

	$feed['ABOUT'] = str_replace('<p>&nbsp;</p>', '', $feed['ABOUT']);

	//echo '<xmp>'.$feed['ABOUT'].'</xmp>';
	
	//preg_match('#^(.*)(<hr(.*) />)#si', str_replace("\r\n", "", $feed['ABOUT']), $tmp);
	preg_match('#^(.+?)(<hr(.+?)/>)(.+?)$#si', str_replace("\r\n", "", $feed['ABOUT']), $tmp);


	if(strlen($tmp[4]) > 0) {
		$feed['ABOUT'] = preg_replace('#<hr(.*)[\"].? />#i', '', $tmp[4]);
		$feed['ABOUT'] = str_replace('<hr />', '', $feed['ABOUT']);
		$feed['ABOUT'] = str_replace('^</p>', '', $feed['ABOUT']);
		//echo '<xmp>';
		//print_r($tmp);
		//echo '</xmp>';
	} else $feed['ABOUT'] = '';
	
	//$feed['ABOUT'] = preg_replace('#^(.*)<hr(.*)[\"].? />#i', '', $feed['ABOUT']);
	$smarty->assign("FeedData", $feed);
	// bind page constructor
	$smarty->assign("PAGER", $site->PageGenerator($page, $feed['PERPAGE'], count($NewsItems)));
}

function smarty_function_MKS_Module_News_Feeds_ByNews($feed_id, $news_id, &$smarty)
{
	global $mysql, $site;
	$feed_id = intval($feed_id); $news_id = intval($news_id);
	if($feed_id > 0) {
		$feed = $mysql->sql("SELECT * FROM ".PREFIX."news_feeds WHERE id = ".$feed_id." AND site_id = ".$_SESSION['CMS_SITE_ID']." AND lang = '".SITELANG."'", 1);
		if(!is_numeric($feed['ID'])) return;
	}
	if($news_id > 0) {
		$item = $mysql->sql("SELECT n.news_id, n.photo_id, n.short_title STitle, n.short_content SText, n.long_title TITLE, long_content CONTENT, n.feed_id , datetm, image FROM ".PREFIX."news n WHERE feed_id = ".$feed_id." AND news_id = ".$news_id, 1);
		if(!is_numeric($item['NEWS_ID'])) return;	
		$item['Date'] = $site->format_date('rM',$item['DATETM']).', '.$site->format_date('rD',$item['DATETM']);
		if(!$item['TITLE']) $item['TITLE'] = $item['STITLE'];
		if(!$item['CONTENT']) $item['CONTENT'] = $item['STEXT'];
		$item['IMAGE'] = unserialize($item['IMAGE']);
		// bind item
		$smarty->assign("Item", $item);
		$smarty->assign("FeedData", $feed);
	}
	
}

function smarty_function_MKS_Module_News_Feeds_ByShow($align, &$smarty)
{
	global $mysql, $site;
	$items = $mysql->sql("SELECT n.news_id id, n.short_title Title, n.short_content Text, n.datetm, n.feed_id, n.image, f.title feed_name FROM ".PREFIX."news n, ".PREFIX."news_feeds f WHERE f.id = n.feed_id AND f.lang = '".SITELANG."' AND f.site_id = ".$_SESSION['CMS_SITE_ID']." AND f.show_align = '".$align."' ORDER BY datetm DESC", 2);
	foreach($items as $nit) {
		$nit['DATE'] = $site->format_date('rM', $nit['DATETM']);
		$NewsItems[] = array("Date"=>$nit['DATE'], "Title"=>$nit['TITLE'], "Text"=>$nit['TEXT'], "FeedName"=>$nit['FEED_NAME'], "Image"=>unserialize($nit['IMAGE']), "ID"=>$nit['ID'], "FEED_ID"=>$nit['FEED_ID']);
	}
	$smarty->assign("Feeds", $NewsItems);
}

function smarty_function_MKS_closetags($html){
  #put all opened tags into an array
  preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU",$html,$result);
  $openedtags=$result[1];

  #put all closed tags into an array
  preg_match_all("#</([a-z]+)>#iU",$html,$result);
  $closedtags=$result[1];
  $len_opened = count($openedtags);
  # all tags are closed
  if(count($closedtags) == $len_opened){
    return $html;
  }
  $openedtags = array_reverse($openedtags);
  # close tags
  for($i=0;$i<$len_opened;$i++) {
    if (!in_array($openedtags[$i],$closedtags)){
      $html .= '</'.$openedtags[$i].'>';
    } else {
      unset($closedtags[array_search($openedtags[$i],$closedtags)]);
    }
  }
  return $html;
}
?>
	