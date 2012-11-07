<?php
/*
Powered by MKS Engine (c) 2005
Created: Ivan S. Soloviev, webmaster@mk-studio.ru
*/

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function start()
	{
		global $db, $admin, $SLANG, $CPANEL_LANG, $SUPPORT_LANG;
		if(!$admin->access('read')) {
			echo $admin->msg_info($CPANEL_LANG['access denied']);
			return;
		}
		// добавление новостной ленты
		if($_REQUEST['feed_id'] != '') return $this->FeedModify($_REQUEST['feed_id']);
		if($_REQUEST['news_id'] != '') return $this->NewsModify($_REQUEST['news_id']);
		if(is_numeric($_REQUEST['feed'])) echo '<p><a href="index.php">'.$SLANG['back to feed'].'</a></p>';
		if($_REQUEST['result'] == "upOK") echo $admin->MSG_INFO($SLANG['info 1']);
		if($_REQUEST['result'] == "deleteOK") echo $admin->MSG_INFO($SLANG['info 2']);
		// список новостых лент
		// print toolbar
		if(!is_numeric($_REQUEST['feed'])) {
			$items[] = array('title'=>$SLANG['add new feed'], 'path'=>'?feed_id=0&InSiteLang='.$_SESSION['AdminLang']);
		} else {
			$items[] = array('title'=>$SLANG['add new'], 'path'=>'?feed='.$_REQUEST['feed'].'&news_id=0&InSiteLang='.$_SESSION['AdminLang']);
		}
		$admin->ToolBar($items);
        if(!is_numeric($_REQUEST['feed'])) {
			$feeds = $db->sql("SELECT
			 distinct ID, CONCAT( title,  ' (', COUNT( b.news_id ) ,  ')' ) TITLE
			 FROM ".PREFIX."news_feeds a LEFT JOIN  ".PREFIX."news b ON a.id = b.feed_id
			 WHERE site_id = ".$_SESSION['SITE_ID']." AND a.lang = '".$_SESSION['AdminLang']."'
			 GROUP BY id, title
			 ORDER BY id", 2);
			$admin->tpl()->assign(array(
                'header' => array(
                    array('#', '5%'),
                    $SLANG['table title 1']
                ),
                'rows' => $feeds,
                'reason_to_delete' => 'feed',
                'title_id' => "feed_id",
                'child_id' => "feed",
                'child_col' => "TITLE"
            ));
		} else {
            $news = $db->sql("SELECT n.NEWS_ID ID,DATE_FORMAT(FROM_UNIXTIME(datetm), '%d.%m.%y') DATETM,  n.SHORT_TITLE TITLE FROM ".PREFIX."news n, ".PREFIX."news_feeds f WHERE f.id = n.feed_id AND f.site_id = ".$_SESSION['SITE_ID']." AND f.lang = '".$_SESSION['AdminLang']."' AND n.feed_id = ".$_REQUEST['feed']." ORDER BY n.datetm DESC", 2);
            $admin->tpl()->assign(array(
                'header' => array(
                    array('#', '5%'),
                    array('Дата', '15%'),
                    $SLANG['table title 1']
                ),
                'rows' => $news,
                'additional_url' => '&feed='.$_REQUEST['feed'],
                'reason_to_delete' => 'news',
                'title_id' => "news_id",
                'child_id' => "news_id",
                'child_col' => "TITLE"
            ));
        }
        echo $admin->tpl()->fetch('table');
	}
	
	/* Работа с лентой новостей */
	function FeedModify($feed)
	{
		global $db, $admin, $SLANG, $feed_id;
		$feed_id = $feed;
		$perp = array('5'=>'5 на страницу','10'=>'10 на страницу','20'=>'20 на страницу','30'=>'30 на страницу','40'=>'40 на страницу','50'=>'50 на страницу');
		$sortby = array('datetm'=>'по дате содания','sort_id'=>'по порядковому номеру');
		$anounce = array('0'=>'не показывать','1'=>'1 новость','2'=>'2 новости','3'=>'3 новости','4'=>'4 новости','5'=>'5 новостей');
		$align = array('left'=>'Левая информационная зона','right'=>'Правая информационная зона');
		$data = $db->sql("SELECT * FROM ".PREFIX."news_feeds WHERE id = ".$feed, 1);
        if (isset($_POST['NEWS']))
		    foreach($_POST['NEWS'] as $key=>$value) $data[$key] = $value;
		echo '<a class="btn" href="index.php">'.$SLANG['back to list'].'</a>';
        echo '<legend>Редактирование ленты</legend>';
        if(strlen(ERROR) > 5) echo ERROR;
		if(!$data['PERPAGE']) $data['PERPAGE'] = '10';
		if($data['SHOW_ANOUNCE'] > 0) $data['ANOUNCE'] = $data['SHOW_AMOUNT'];
		echo '<form name="MainForm" action="?feed_id='.$feed.'&InSiteLang='.$_SESSION['AdminLang'].'" method="post" enctype="multipart/form-data"><table cellspacing="0" cellpadding="3" border="0" class="Manage">';
		echo '<tr><td class="RightTD" style="width: 30%;">Название<font color="red">*</font>:</td><td><input type="text" name="NEWS[TITLE]" value="'.$data['TITLE'].'" class="field" maxlength="255" style="width: 100%;"></td></tr>';
		echo '<tr><td class="RightTD">Кол-во новостей:</td><td><select name="NEWS[PERPAGE]" size="1" class="field" style="width: 200px;">'.$admin->select_from_array($perp, $data['PERPAGE']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Сортировка:</td><td><select name="NEWS[SORTBY]" size="1" class="field" style="width: 200px;">'.$admin->select_from_array($sortby, $data['SORTBY']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Анонс ленты:</td><td><select name="NEWS[ANOUNCE]" size="1" class="field" style="width: 200px;">'.$admin->select_from_array($anounce, $data['ANOUNCE']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Позиционирование анонса:</td><td><select name="NEWS[SHOW_ALIGN]" size="1" class="field" style="width: 200px;">'.$admin->select_from_array($align, $data['SHOW_ALIGN']).'</select></td></tr>';
		echo '<tr><td class="RightTD">Описание:</td><td>'.$admin->htmlarea("NEWS[ABOUT]",$data['ABOUT'],300).'</td></tr>';
		if($data['IMAGE'] != "") {
			echo '<tr><td class="RightTD">'.$SLANG['edit image title'].':</td><td><img src="'.$_SERVER['CMS_ROOT_URL'].'thumbnails.php?picname=images/mks/news/'.$data['IMAGE'].'&w=150" align="absmiddle" alt="" border="0"/></td></tr>';
		}
		echo '<tr><td class="RightTD">Путь к изображению:</td><td><input type="file" name="IMG" value="" class="field" style="width: 100%;"/></td></tr>';
		echo '<tr><td class="RightTD">Показывать на страницах:</td><td><table cellspacing="1" cellpadding="3" bgColor="#D9D9D9" width="100%"><tr style="background-color: #f0f0f0; font-weight: bold; height: 30px;"><td align="center" width="80%">Название страницы</td><td align="center">Показывать</td></tr>';
		$this->AllPages();
		echo '<tr style="background-color: white;"><td></td><td align="center"><a href="javascript: void(0);" onClick="javascript: PagesAll(document.MainForm);">Выбрать все</a></td></tr>';
		echo '</table></td></tr>';
		echo '<tr><td class="RightTD">&nbsp;</td><td><input type="hidden" name="NEWS[OLD_IMAGE]" value="'.$data['IMAGE'].'"/><input type="hidden" name="refurl" value="'.$_REQUEST['refurl'].'"/><input type="submit" name="FeedSave" value="'.$SLANG['btn save'].'" class="btn btn-primary" '.(!$admin->access('edit') ? 'disabled' : '').'/></td></tr>';
		echo '</table></form>';
	}
	
	/* Работа с выбранной новостью */
	function NewsModify($news_id = 0)
	{
		global $db, $admin, $SLANG;
		if($news_id > 0) {
			$data = $db->sql("SELECT datetm, photo_id, short_title, short_content, long_title, long_content, image \"OLD_IMAGE\", show_left, show_right FROM ".PREFIX."news WHERE news_id = ".$news_id." AND feed_id = ".$_REQUEST['feed'],1);
		}
        if (sizeof($_POST['NEWS']) > 0)
		    foreach($_POST['NEWS'] as $key=>$value) $data[$key] = $value;
		if($data['DATETM'] == "") $data['DATETM'] = time();
		if($_POST['NEWS']['DATETM']) $data['DATETM'] = mktime(0,0,0,substr($data['DATETM'],3,2),substr($data['DATETM'],0,2),substr($data['DATETM'],6,4));
		echo '<a class="btn" href="?feed='.$_REQUEST['feed'].'">&laquo; '.$SLANG['back to list'].'</a>';

        echo '<legend>Редактирование свойств элемента</legend>';
        if(strlen(ERROR) > 5) echo ERROR;
		echo '<form name="MainForm" action="?feed='.$_REQUEST['feed'].'&news_id='.$news_id.'" method="post" enctype="multipart/form-data"><input type="hidden" name="NEWS[SHOW_LEFT]" value="0"><input type="hidden" name="NEWS[SHOW_RIGHT]" value="0"><table cellspacing="0" cellpadding="3" border="0" class="Manage">';
		echo '<tr><td class="RightTD" style="width: 20%;">Дата:</td><td>
		<div class="input-append">
		<input type="text" name="NEWS[DATETM]" value="'.date('d.m.Y',$data['DATETM']).'" class="field" style="width: 80px;" readonly>
		<a href="#" class="btn" onClick="showCal(\'DateNews\');"><img src="/files/images/calendar.png" alt="Выберите дату" border="0"></a>
		</div>
		</td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['edit short title'].'<font color="red">*</font>:</td><td><input type="text" name="NEWS[SHORT_TITLE]" value="'.$data['SHORT_TITLE'].'" class="field" style="width: 100%;" maxlength="200"/></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['edit short desc'].'<font color="red">*</font>:</td><td><textarea name="NEWS[SHORT_CONTENT]" rows=5 cols=40 class="field" style="width: 100%; height: auto; background-image: none;">'.str_replace('<br />','',$data['SHORT_CONTENT']).'</textarea></td></tr>';
		echo '<tr><td class="RightTD">&nbsp;</td><td>'.$SLANG['edit hint 1'].'</td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['edit title'].':</td><td><input type="text" name="NEWS[LONG_TITLE]" value="'.$data['LONG_TITLE'].'" class="field" style="width: 100%;" maxlength="200"/></td></tr>';
		echo '<tr><td class="RightTD">'.$SLANG['edit desc'].':</td><td>'.$admin->htmlarea("NEWS[LONG_CONTENT]",$data['LONG_CONTENT'],300).'</td></tr>';
		echo '<tr><td class="RightTD">Ссылка на фотогалерею:</td><td><select name="NEWS[PHOTO_ID]" class="field" size="1" style="width: 100%;"><option value="0">не выбрано</option>';
		$ph = $db->sql("SELECT oid, name FROM ".PREFIX."photo_objects WHERE type='gallery' AND parent_id = 5 AND lang = '".$_SESSION['AdminLang']."' ORDER BY sort_id", 2);
		foreach($ph as $p) echo '<option value="'.$p['OID'].'" '.($data['PHOTO_ID'] == $p['OID'] ? 'selected' : '').'>'.$p['NAME'].'</option>';
		echo '</select></td></tr>';
		$imgs = unserialize($data['OLD_IMAGE']);
		if($imgs && sizeof($imgs) > 0) {
			echo '<tr><td class="RightTD">'.$SLANG['edit image title'].':</td><td>';
			foreach($imgs as $img) {
				echo '<a href="?feed='.$_REQUEST['feed'].'&news_id='.$news_id.'&InSiteLang='.$_SESSION['AdminLang'].'&dimg='.$img.'" title="Кликните для удаления" onClick="return confirm(\'Фото будет удалено. Продолжить?\');"><img src="'.$_SERVER['CMS_ROOT_URL'].'thumbnails.php?picname=images/mks/news/'.$img.'&w=120" align="absmiddle" alt="Кликните для удаления" border="0"/></a>&nbsp;';
			}
			echo '</td></tr>';
		}
		echo '<tr><td class="RightTD">'.$SLANG['edit image'].':</td><td><input type="file" name="IMG" value="" class="field" style="width: 100%;"/></td></tr>';
		echo '<tr><td class="RightTD">&nbsp;</td><td>
            <label class="checkbox">
		    <input type="checkbox" name="AutoResize" value="1" ' . ($_POST['AutoResize'] == 1 ? 'checked' : '') . '>' . $SLANG['edit hint 2'] . '
		    </label><label class="checkbox">
		    <input type="checkbox" name="SendToRecipients" value="1" ' . ($_POST['SendToRecipients'] == 1 ? 'checked' : '') . '>Разослать подписчикам
		    </label><label class="checkbox">
		    <input type="checkbox" name="NEWS[SHOW_LEFT]" value="1" ' . ($data['SHOW_LEFT'] == 1 ? 'checked' : '') . '>Показывать на главной странице в левом блоке
		    </label><label class="checkbox">
		    <input type="checkbox" name="NEWS[SHOW_RIGHT]" value="1" ' . ($data['SHOW_RIGHT'] == 1 ? 'checked' : '') . '>Показывать на главной странице в правом блоке
		    </label><label class="checkbox">
		    <input type="hidden" name="refurl" value="' . $_REQUEST['refurl'] . '"/></label><input type="hidden" name="NEWS[OLD_IMAGE]" value=\''.$data['OLD_IMAGE'].'\'/><input type="submit" name="NewsSave" value="'.$SLANG['btn save'].'" class="btn btn-primary" '.(!$admin->access('edit') ? 'disabled' : '').'/></td></tr>';
		echo '</table></form>';
		
	}
	
	/* Создание RSS фида */
	function MakeRss()
	{
		global $db, $SLANG, $admin;
		$res = $db->sql("SELECT page_path FROM ".PREFIX."pages WHERE site_id = ".$_SESSION['SITE_ID']." AND page_type = 'news'", 1);
		$NEWS_URL = $res['PAGE_PATH'];
		$NEWS_URL = str_replace('/','*',$NEWS_URL);
		$NEWS_URL = ereg_replace('^\*|\*$','',$NEWS_URL);		
		$news = $db->sql("SELECT datetm, short_title title, short_content content, news_id, lang FROM ".PREFIX."news WHERE site_id = ".$_SESSION['SITE_ID']." ORDER BY datetm DESC", 2);
		$cont['title'] = $SLANG['rss title'];
		$cont['link'] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['CMS_ROOT_URL'];
		$cont['description'] = $SLANG['rss desc'].$_SERVER['HTTP_HOST'];
		$cont['lastBuildDate'] = date("d M Y H:i:s")." +0300";
		foreach($news as $n) {
			$sct[$n['LANG']][]['item'] = array("title"=>$n['TITLE'],"link"=>"http://".$_SERVER['HTTP_HOST'].$_SERVER['CMS_ROOT_URL'].$n['LANG']."/".$NEWS_URL."*".$n['FEED_ID'].",".$n['NEWS_ID'].".html","description"=>stripslashes($news[$i]['newss']),"pubDate"=>date("d.m.Y H:i:s",$n['DATETM'])." +0300","guid"=>"http://".$_SERVER['HTTP_HOST'].$_SERVER['CMS_ROOT_URL'].$n['LANG']."/".$NEWS_URL."*".$n['FEED_ID'].",".$n['NEWS_ID'].".html");
		}
		foreach($sct as $key=>$val) {
			$total = array_merge($cont,$val);
			$docs = $admin->BuildRSS($total);
			$fp = fopen(CMS_ROOT_DIR.$_SESSION['SITE_NAME']."_news_".$_REQUEST['feed']."_".$key.".rss", "w");
			fwrite($fp, $docs);
			fclose($fp);
		}
	}
	
	/* Список доступных страниц */
	function AllPages($parent = 0, $cnt = 0)
	{
		global $db, $cnt, $feed_id, $q;
		$pages=$db->sql("SELECT p.page_id, p.page_parent, c.record_id rid, c.menu_title, p.page_path, p.page_order FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_parent = ".$parent." AND c.lang = '".$_SESSION['AdminLang']."' AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_order", 2);
		for($i=0;$i<count($pages);$i++) 
		{
			$q++;
			$p = $db->sql("SELECT count(*) cnt FROM ".PREFIX."news_pages WHERE feed_id = ".$feed_id." AND page_id = ".$pages[$i]['RID'], 1);
			echo '<tr style="background-color: white;"><td>'.str_repeat("&nbsp;",($cnt*5)).$pages[$i]['MENU_TITLE'].'</td><td align="center"><input type="hidden" name="ALLP['.$q.']" value="'.$pages[$i]['RID'].'"><input type="checkbox" name="SHOWP['.$q.']" value="'.$pages[$i]['RID'].'" '.($p['CNT'] == 0 ? 'checked' : '').'></td></tr>';			
			$cnt++;
			$this->AllPages($pages[$i]['PAGE_ID'], $cnt);
			$cnt--;
		}		
	}
}

global $SLANG;
$admin=new admin("NewsManager");
$admin->WorkSpaceTitle = $SLANG['Page Header'];

/* Изменение сортировки ленты */
if($_REQUEST['sort'] && $_REQUEST['feed_id'] > 0) {
	if($admin->access('edit')) {
		// вытащим текущую сортировку
		$s = $db->sql("SELECT sort_id sid FROM ".PREFIX."news_feeds WHERE id = ".$_REQUEST['feed_id'], 1);
		switch($_REQUEST['sort']) {
			case "up":
				$sort = $s['SID'] - 1;
				$db->sql("UPDATE ".PREFIX."news_feeds SET sort_id = ".$s['SID']." WHERE sort_id = ".($sort)." AND site_id = ".$_SESSION['SITE_ID']." AND lang = '".$_SESSION['AdminLang']."'");
				break;
			case "down":
				$sort = $s['SID'] + 1;
				$db->sql("UPDATE ".PREFIX."news_feeds SET sort_id = ".$s['SID']." WHERE sort_id = ".($sort)." AND site_id = ".$_SESSION['SITE_ID']." AND lang = '".$_SESSION['AdminLang']."'");
				break;
		}
		$db->sql("UPDATE ".PREFIX."news_feeds SET sort_id = ".$sort." WHERE id = ".$_REQUEST['feed_id']." AND site_id = ".$_SESSION['SITE_ID']." AND lang = '".$_SESSION['AdminLang']."'");
		if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
		else header("Location: ?InSiteLang=".$_SESSION['AdminLang']);
		exit;
	}
}

/* Изменение сортировки новости */
if($_REQUEST['sort'] && $_REQUEST['news_id'] > 0) {
	if($admin->access('edit')) {
		// вытащим текущую сортировку
		$s = $db->sql("SELECT sort_id sid FROM ".PREFIX."news WHERE news_id = ".$_REQUEST['news_id'], 1);
		switch($_REQUEST['sort']) {
			case "up":
				$sort = $s['SID'] + 1;
				$db->sql("UPDATE ".PREFIX."news SET sort_id = ".$s['SID']." WHERE sort_id = ".($sort)." AND feed_id = ".$_REQUEST['feed']);
				break;
			case "down":
				$sort = $s['SID'] - 1;
				$db->sql("UPDATE ".PREFIX."news SET sort_id = ".$s['SID']." WHERE sort_id = ".($sort)." AND feed_id = ".$_REQUEST['feed']);
				break;
		}
		$db->sql("UPDATE ".PREFIX."news SET sort_id = ".$sort." WHERE news_id = ".$_REQUEST['news_id']." AND feed_id = ".$_REQUEST['feed']);
		if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
		else header("Location: ?feed=".$_REQUEST['feed']."&InSiteLang=".$_SESSION['AdminLang']);
		exit;
	}
}

/* Добавление (изменение) ленты новостей */
if($_POST['FeedSave']) {
	if($admin->access('edit')) {
		foreach($_POST['NEWS'] as $key=>$value) $$key = $value;
		if(!$TITLE) $err[] = $SLANG['error 2'];
		if(is_array($err)) {
			define('ERROR',$admin->err_info('<li>'.implode('<li>',$err)));
		} else {
			if($ANOUNCE != 0) {
				$SHOW_ANOUNCE = 1;
				$SHOW_AMOUNT = $ANOUNCE;
			} else {
				$SHOW_ANOUNCE = 0;
				$SHOW_AMOUNT = 0;
			}
			if($_REQUEST['feed_id'] == 0) {
				$res = $db->sql("SELECT max(sort_id) sid FROM ".PREFIX."news_feeds WHERE site_id = ".$_SESSION['SITE_ID']." AND lang = '".$_SESSION['AdminLang']."'", 1);
				$sort_id = $res['SID'] + 1;
				$db->sql("INSERT INTO ".PREFIX."news_feeds SET site_id = ".$_SESSION['SITE_ID'].", lang = '".$_SESSION['AdminLang']."', title = '".$admin->my_str_replace($TITLE)."', about = '".str_replace("'","\'",$ABOUT)."', perpage = ".$PERPAGE.", sortby = '".$SORTBY."', show_anounce = ".$SHOW_ANOUNCE.", show_amount = ".$SHOW_AMOUNT.", show_align = '".$SHOW_ALIGN."', sort_id = ".$sort_id);
				$_REQUEST['feed_id'] = $db->GetLastID();
			} else {
				$db->sql("UPDATE ".PREFIX."news_feeds SET title = '".$admin->my_str_replace($TITLE)."', about = '".str_replace("'","\'",$ABOUT)."', perpage = ".$PERPAGE.", sortby = '".$SORTBY."', show_anounce = ".$SHOW_ANOUNCE.", show_amount = ".$SHOW_AMOUNT.", show_align = '".$SHOW_ALIGN."' WHERE id = ".$_REQUEST['feed_id']);
			}
			
			// сохраняем инфу о страницах
			$db->sql("DELETE FROM ".PREFIX."news_pages WHERE feed_id = ".$_REQUEST['feed_id']);
			for($i=0;$i<count($_POST['ALLP']);$i++) {
				if($_POST['ALLP'][$i] != $_POST['SHOWP'][$i]) $db->sql("INSERT INTO ".PREFIX."news_pages SET feed_id = ".$_REQUEST['feed_id'].", page_id = ".$_POST['ALLP'][$i]);
			}

			// сохраняем изображение			
			if($_FILES['IMG']['name'] != "") {
				if (ereg('png',$_FILES['IMG']['type']))  	{ $ext = 'png'; }
				if (ereg('jpeg',$_FILES['IMG']['type'])) 	{ $ext = 'jpg'; }
				if (ereg('gif',$_FILES['IMG']['type']))  	{ $ext = 'gif'; }
				$OLD_IMAGE = md5('Feed_'.$_REQUEST['feed_id']).'.'.$ext;
				@mkdir(CMS_ROOT_DIR."images/mks/news",0777);
				if($_POST['AutoResize'] == 1) 
					$admin->image_action($_FILES['IMG']['tmp_name'], CMS_ROOT_DIR."images/mks/news/".$OLD_IMAGE, 100, 75, 100);
				else 
					move_uploaded_file($_FILES['IMG']['tmp_name'], CMS_ROOT_DIR."images/mks/news/".$OLD_IMAGE);
				$db->sql("UPDATE ".PREFIX."news_feeds SET image = '".$OLD_IMAGE."' WHERE id = ".$_REQUEST['feed_id']);
                @chmod(CMS_ROOT_DIR."images/mks/news/".$OLD_IMAGE, 0666);
            }
			if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
			else header("Location: ?result=upOK");
			exit;
		}
	}
}
/* Добавление (изменение) новости */
if($_POST['NewsSave']) {
	if ($admin->access('edit')) {
		foreach($_REQUEST['NEWS'] as $key=>$value) $$key = $value;
		if($SHORT_TITLE == "") $err[] = $SLANG['error 3'];
		if($SHORT_CONTENT == "") $err[] = $SLANG['error 4'];
		if($_FILES['IMG']['name'] != "" && !eregi('jpeg|gif|png', $_FILES['IMG']['type'])) $err[] = $SLANG['error 5'];
		if(is_array($err)) {
			define("ERROR", $admin->err_info('<li>'.implode('<li>',$err)));
		} else {
			$datetm = mktime(0,0,0,substr($DATETM,3,2),substr($DATETM,0,2),substr($DATETM,6,4));
			if($_REQUEST['news_id'] == 0) {
				$res = $db->sql("SELECT max(sort_id) sid FROM ".PREFIX."news WHERE feed_id = ".$_REQUEST['feed'], 1);
				$sort = $res['SID'] + 1;
				$db->sql("INSERT INTO ".PREFIX."news SET feed_id = ".$_REQUEST['feed'].", photo_id = ".$PHOTO_ID.", datetm = '".$datetm."', short_title = '".$admin->my_str_replace($SHORT_TITLE)."', short_content = '".$admin->my_str_replace($SHORT_CONTENT)."', long_title = '".$admin->my_str_replace($LONG_TITLE)."', long_content = '".str_replace("'","\'",$LONG_CONTENT)."', sort_id = ".$sort.", show_left = ".$SHOW_LEFT.", show_right = ".$SHOW_RIGHT);
				$_REQUEST['news_id'] = $db->GetLastID();
			} else {
				$db->sql("UPDATE ".PREFIX."news SET short_title = '".$admin->my_str_replace($SHORT_TITLE)."', photo_id = ".$PHOTO_ID.", short_content = '".$admin->my_str_replace($SHORT_CONTENT)."', datetm = '".$datetm."', long_title = '".$admin->my_str_replace($LONG_TITLE)."', long_content = '".str_replace("'","\'",$LONG_CONTENT)."', show_left = ".$SHOW_LEFT.", show_right = ".$SHOW_RIGHT." WHERE news_id = ".$_REQUEST['news_id']);
			}
			if($_FILES['IMG']['name'] != "") {
				if (ereg('png',$_FILES['IMG']['type']))  	{ $ext = 'png'; }
				if (ereg('jpeg',$_FILES['IMG']['type'])) 	{ $ext = 'jpg'; }
				if (ereg('gif',$_FILES['IMG']['type']))  	{ $ext = 'gif'; }
				$imgName = md5(time()).'.'.$ext;
				@mkdir(CMS_ROOT_DIR."images/mks/news",0777);
				if($_POST['AutoResize'] == 1) 
					$admin->image_action($_FILES['IMG']['tmp_name'], CMS_ROOT_DIR."images/mks/news/".$imgName, 100, 75, 100);
				else 
					move_uploaded_file($_FILES['IMG']['tmp_name'], CMS_ROOT_DIR."images/mks/news/".$imgName);
				chmod(CMS_ROOT_DIR."images/mks/news/".$imgName, 0666);
				$tmp = unserialize($OLD_IMAGE);
				$tmp[] = $imgName;
				$OLD_IMAGE = serialize($tmp);
				$db->sql("UPDATE ".PREFIX."news SET image = '".$OLD_IMAGE."' WHERE news_id = ".$_REQUEST['news_id']);
			}
			
			// рассылаем подписчикам
			if($_POST['SendToRecipients']) {
				$emails = $db->sql("SELECT email, code FROM ".PREFIX."news_emails WHERE status = 1 AND feed_id = ".$_REQUEST['feed'], 2);
				if($LONG_TITLE) $title = $LONG_TITLE; else $title = $SHORT_TITLE;
				if($LONG_CONTENT) $content = $LONG_CONTENT; else $content = $SHORT_CONTENT;
				$message = $content;
				foreach($emails as $r) {
					$link = "http://".$_SERVER['HTTP_HOST']."/inform-news.html?unsubcode=".$r['CODE'];
					mail($r['EMAIL'],'Почтовая рассылка "'.$title.'"', $message."<br/><br/>----<br/><br/>Для того, чтобы отписаться от данной подписки, перейдите по ссылке: <a href=\"".$link."\">".$link."</a><br/><br/>С Уважением,<br/>Администрация сайта", "From: site@".$_SERVER['HTTP_HOST']."\r\nContent-type: text/html; charset=windows-1251");
				}
			}
			if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
			else header("Location: ?feed=".$_REQUEST['feed']."&result=upOK");
			exit;
		}
	}
}

/* Удаление ленты */
if($_REQUEST['reason'] == "feed" && is_numeric($_REQUEST['delete'])) {
	if($admin->access('kill')) 
	{
		$db->sql("DELETE FROM ".PREFIX."news_feeds WHERE id = ".$_REQUEST['delete']);
		$res = $db->sql("SELECT * FROM ".PREFIX."news WHERE feed_id = ".$_REQUEST['delete'],2);
		foreach($res as $r) {
			$db->sql("DELETE FROM ".PREFIX."news WHERE news_id = ".$r['NEWS_ID']);
			if($n['IMAGE'] != "") unlink(CMS_ROOT_DIR."images/mks/news/".$r['IMAGE_NAME']);
		}
		if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
		else header("Location: ?result=deleteOK&InSiteLang=".$_SESSION['AdminLang']);
		exit;
	}
}

/* Удаление новости */
if($_REQUEST['reason'] == "news" && is_numeric($_REQUEST['delete'])) {
	if($admin->access('kill')) 
	{
		$n=$db->sql("SELECT image FROM ".PREFIX."news WHERE news_id = ".$_REQUEST['delete'], 1);
		if($n['IMAGE'] != "") unlink(CMS_ROOT_DIR."images/mks/news/".$n['IMAGE_NAME']);
		$db->sql("DELETE FROM ".PREFIX."news WHERE news_id = ".$_REQUEST['delete']);
		if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
		else header("Location: ?feed=".$_REQUEST['feed']."&result=deleteOK&InSiteLang=".$_SESSION['AdminLang']);
		exit;
	}
}

/* Удаление изображения новости */
if($_REQUEST['dimg'] != "") {
	if($admin->access('kill')) 
	{
		@unlink(CMS_ROOT_DIR.'images/mks/news/'.$_REQUEST['dimg']);
		$res = $db->sql("SELECT image FROM ".PREFIX."news WHERE news_id = ".$_REQUEST['news_id'], 1);
		$imgs = unserialize($res['IMAGE']);
		$newImg = array();
		foreach($imgs as $img) if($img != $_REQUEST['dimg']) $newImg[] = $img;
		$db->sql("UPDATE ".PREFIX."news SET image = '".serialize($newImg)."' WHERE news_id = ".$_REQUEST['news_id']);
		header("Location: ?feed=".$_REQUEST['feed']."&news_id=".$_REQUEST['news_id']."&InSiteLang=".$_SESSION['AdminLang']);
	}
}

$admin->main();
