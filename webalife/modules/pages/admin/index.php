<?php
/**
 *
 */

require_once CMS_CLASSES."admin.class.php";

class handler
{
	function start()
	{
		global $db, $admin, $subpath, $SLANG, $CPANEL_LANG;
		if(!$admin->access('read')) {
			echo $admin->msg_info($CPANEL_LANG['access denied']);
			return;
		}
		$subpath = $_REQUEST['path'];

		if($_REQUEST['page_id'] != "") {
			$this->ManagePage($_REQUEST['page_id']);
			return;
		}

		if($_REQUEST['result'] == 'NoSelect') echo $admin->err_info('<li>Необходимо выбрать хотя бы один пункт меню!');
		echo "<form name=\"FormMain\" action=\"\" method=\"post\"><input type=\"hidden\" name=\"PageAction\" value=\"true\"><div class=\"tree\">\r\n";
		echo '<script language="javascript" type="text/javascript">
				$(function() {
				a = new dTree(\'a\');
				a.config.useCookies=true;
				a.add(0,-1,\'Структура сайта\',\'\',\'\');
				';
		echo $this->PageTree('');
		echo '
				$(\'div.tree\').html(a.toString());
				a.openTo(1);
				r = $.cookie(\'CTree\');
				if(r != null) {
					$(\'#d\' + r).css(\'display\',  \'block\');
				}
				setNodeActions();
				});
				</script>
				</div></form>';
	}
	
	/* Построение дерева страниц */
	function PageTree($path, $count = -1)
	{
		global $db, $admin, $SLANG, $row;
		if($path != "") {
			$p = $db->sql("SELECT page_id, page_parent FROM ".PREFIX."pages WHERE page_path = '".$path."' AND site_id = ".$_SESSION['SITE_ID'], 1);
			//echo $db->getsql();
			$parent = $p['PAGE_ID'];
			if(!$parent) $parent = '0';
		} else $parent = '0';
		if(!$row) $row = 0;
		$count++;
		$menu=$db->sql("SELECT c.menu_title name_menu, c.record_id rid, p.page_path, p.page_parent pid, p.page_id id, p.page_order, p.page_active, p.redir_to FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND c.lang = '".DEFAULT_LANG."' AND p.page_parent = ".$parent." AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_order", 2);
		//echo $db->getsql();
        for ($i = 0; $i < count($menu); $i++) {
            if ($menu[$i]['PAGE_ACTIVE'] == "1") $active = "активна"; else $active = "заблокирована";
            $up = true;
            $down = true;
            if ($i == 0) {
                $up = false;
            }
            if ($i == count($menu) - 1) {
                $down = false;
            }
            $foobar = "";
            $adding = "";
			if(strlen($menu[$i]['REDIR_TO']) && ($menu[$i]['REDIR_TO'] != '0')) {
				if(is_numeric($menu[$i]['REDIR_TO'])) {
					$rrr = $db->sql("SELECT c.menu_title mt FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.site_id = ".$_SESSION['SITE_ID']." AND p.page_id = ".$menu[$i]['REDIR_TO']." AND c.lang = '".SLANG."'", 1);
					$RedPage = 'страницу &laquo;'.$rrr['MT'].'&raquo;';
				} else {
					$RedPage = $menu[$i]['REDIR_TO'];
				}
				$adding = '&nbsp;<em>(Переадресация на '.$RedPage.')</em>&nbsp;';
			}
			if($up) {
				$foobar .= "<a href='?page_id=".$menu[$i]['ID']."&sort=up'>";
			    $foobar .= "<span class='icon-arrow-up' title='Поднять наверх'></span>";
				$foobar .= '</a>';
                $foobar .= '&nbsp;&nbsp;&nbsp;';
            }
			if($down) {
				$foobar .= "<a href='?page_id=".$menu[$i]['ID']."&sort=down'>";
			    $foobar .= "<span class='icon-arrow-down' title='Опустить вниз'></span>";
			    $foobar .= '</a>';
			    $foobar .= '&nbsp;&nbsp;&nbsp;';
            }

            if($admin->access('edit')) {
				$foobar .= "<a href='?page_id=0&pid=".$menu[$i]['ID']."' title='".$SLANG['doc add']."'><span class='icon-plus' title='".$SLANG['doc add']."'></span></a>&nbsp;&nbsp;&nbsp;";
			    $foobar .= "<a href='?page_id=".$menu[$i]['ID']."'><span class='icon-edit' title='Редактировать'></span></a>&nbsp;&nbsp;&nbsp;";
            }

            if($admin->access('kill') && $parent > 0) {
                $foobar .= "<a href='?page_id=".$menu[$i]['ID']."&action=delete' onclick='return deleteConfirm();'><span class='icon-remove' title='Удалить'></span></a>";
            }

			echo "\r\na.add(".$menu[$i]['ID'].", ".$menu[$i]['PID'].",'".($menu[$i]['PAGE_ACTIVE'] == 0 ? "<span style=\"color: #909090\">" : "").$menu[$i]['NAME_MENU'].$adding.($menu[$i]['PAGE_ACTIVE'] == 0 ? "</span>" : "")."','javascript: void(0);',\"".$foobar."\");";
			$row++;
			$this->PageTree($menu[$i]['PAGE_PATH'], $count);
		}
		$count--;
	}

	/* Панель быстрой навигации */
	function ExtraPanel($pathOri)
	{
		global $db, $menu, $admin;
		$arr = split("/", $pathOri);
		$path = "/";
		for($i=0;$i<count($arr)-1;$i++) {
			$str = $arr[$i];
			if($str == "" && $path != "/") $str = "/"; 
			$path .= $str;
			if(!eregi('/$',$path)) $path .= "/";
			$menu=$db->sql("SELECT c.menu_title, p.page_path FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND c.lang = 'rus' AND p.page_path = '".$path."'", 1);
			if($path == $pathOri) {
				$m[] = $menu['MENU_TITLE'];
			} else {
				$m[] = '<a href="page_list.php?path='.$menu['PAGE_PATH'].'">'.$menu['MENU_TITLE'].'</a>';
			}
		}	
		return $m;	
	}
	
	/* Содержание страницы */
	function ManagePage($page_id = 0) {
		global $db, $admin, $subpath, $text, $SUPPORT_LANG;
		if($_REQUEST['InLang'] == "") $_REQUEST['InLang'] = DEFAULT_LANG;
		$InLang = $_REQUEST['InLang'];
		if($_REQUEST['result'] == "addOK") echo $admin->MSG_INFO("<li>Страница успешно добавлена!</li>");
		if($_REQUEST['result'] == "updateOK") echo $admin->MSG_INFO("<li>Страница успешно обновлена!</li>");
		$PAGE_ADDIT = $PAGE_ACTIVE = array("checked","");
		if($page_id > 0) {
			$r = $db->sql("SELECT * FROM ".PREFIX."pages WHERE page_id = ".$page_id, 1);
			foreach($r as $key => $value) {$$key=$value;}
		}
        if (sizeof($_POST['PAGE']) > 0)
		    foreach($_POST['PAGE'] as $key => $value) $$key=$value;
		if(strlen(ERROR_INFO) > 10) echo ERROR_INFO;
		if($PAGE_ACTIVE == "0") $PAGE_ACTIVE = array("","checked"); else $PAGE_ACTIVE = array("checked","");
		if($INBOT == "1") $INBOT = array("checked",""); else $INBOT = array("","checked");
		if($PAGE_ADDIT == "0") $PAGE_ADDIT = array("","checked"); else $PAGE_ADDIT = array("checked","");
		
		echo '<form name="MainForm" action="?page_id='.$page_id.'&subpath='.$_REQUEST['subpath'].'" method="post" enctype="multipart/form-data"><table cellspacing="0" cellpadding="3" border="0" class="Manage" style="width: 100%;">';
		if($page_id > 0) {
			echo '<tr><td class="RightTD">Быстрый переход к:</td><td>';
			echo '<select name="PAGE[PAGE_PARENT]" size="1" class="field" onChange="document.location=\'?page_id=\'+this.value" style="width: 300px;"><option value="0">Создать страницу</option><option value="0">------------------------</option>'.$this->_list_pages(0, -1, $page_id).'</select>';
			echo '</td></tr>';
		}
		echo '<tr><td class="RightTD" colspan="2" style="padding-top: 15px;"><legend>Основные данные</legend></td></tr>';
		echo '<tr><td class="RightTD" width="25%">Родительская страница:</td><td>';
		echo '<input type="hidden" name="PAGE[PAGE_PARENT]" value="'.$PAGE_PARENT.'">';
		if($page_id == 0) echo '<select name="PAGE[PAGE_PARENT]" size="1" class="field" style="width: 300px;">'.$this->_list_pages(0, 0, ($_GET['pid'] > 0 ? $_GET['pid'] : '')).'</select>';
		else echo $this->_list_pages($PAGE_PARENT, 0, '', true);
		echo '</td></tr>';
		echo '<tr><td class="RightTD">Шаблон страницы:</td><td>';
		echo '<select name="PAGE[PAGE_TMPLT]" size="1" class="field" style="width: 300px;">'.$this->_list_templates($PAGE_TMPLT).'</select>';
		echo '</td></tr>';
		echo '<tr><td class="RightTD">Тип содержания:</td><td>';
		echo '<select name="PAGE[PAGE_TYPE]" size="1" class="field" style="width: 300px;">'.$this->_list_types($PAGE_TYPE).'</select>';
		echo '</td></tr>';
		echo '<tr><td class="RightTD">Псевдоним страницы'.($page_id == 0 ? '<font color="red">*</font>' : '').':</td><td>';
		echo '<input type="hidden" name="PAGE[PAGE_PATH]" value="'.$PAGE_PATH.'">';
		if($page_id == 0) echo '<input type="text" name="PAGE[PAGE_PATH]" value="'.$PAGE_PATH.'" class="field" style="width: 300px;" maxlength="100"/>';
		else {
			$PregPath = str_replace('/','-',$PAGE_PATH);
			$PregPath = ereg_replace('^-','/',$PregPath);
			$PregPath = ereg_replace('-$','',$PregPath);
			if($PregPath == '/') $PregPath = '/index';
			echo '<a href="http://'.$_SERVER['HTTP_HOST']."/".$InLang.$PregPath.'.html" target="NewWnd">http://'.$_SERVER['HTTP_HOST']."/".$InLang.$PregPath.'.html</a>';
		}
		echo '</td></tr>';		
		echo '<tr><td class="RightTD">Статус страницы:</td><td>
		<label class="radio">
		    <input type="radio" name="PAGE[PAGE_ACTIVE]" value="1" '.$PAGE_ACTIVE[0].'/> активна
        </label>
        <label class="radio">
		    <input type="radio" name="PAGE[PAGE_ACTIVE]" value="0" '.$PAGE_ACTIVE[1].'/> временно заблокирована
		</label></td></tr>';
		echo '<tr><td class="RightTD">В нижнем меню:</td><td>
		<label class="radio">
		    <input type="radio" name="PAGE[INBOT]" value="1" '.$INBOT[0].'/> показывать
		</label>
		<label class="radio">
		    <input type="radio" name="PAGE[INBOT]" value="0" '.$INBOT[1].'/> не показывать
		</label></td></tr>';
		echo '<tr><td class="RightTD">Переадресация на страницу:</td><td>';
		$text = "";
		echo '<select name="PAGE[REDIR_TO]" size="1" class="field" style="width: 300px;"><option value="0">редиректа нет</option>'.$this->_list_pages(0, -1, '', false, $REDIR_TO).'</select>&nbsp;';
		echo '</td></tr>';
		if(!$ABS_REDIR && !is_numeric($REDIR_TO)) $ABS_REDIR = $REDIR_TO;
		echo '<tr><td class="RightTD">Переадресация на URL:</td><td><input type="text" name="PAGE[ABS_REDIR]" value="'.$ABS_REDIR.'" class="field" style="width: 300px;"></td></tr>';
		echo '<tr><td class="RightTD">Версия для печати:</td><td>';
		echo '<select name="PAGE[PAGE_PRINT]" size="1" class="field" style="width: 300px;">'.$admin->select_from_array(array('не показывать','показывать'),$PAGE_PRINT).'</select>';
		echo '</td></tr>';
		if($page_id > 0) {
			// прогрузка данных в соответствии с языком
			echo '<tr><td class="RightTD">Переместить страницу:</td><td>';
			echo '<select name="PAGE[MOVE_TO]" size="1" class="field" style="width: 300px;"><option value="">выберите родительскую страницу...</option>'.$this->_move_pages(0, -1, $page_id).'</select>';
			echo '</td></tr>';
			$data = $db->sql("SELECT * FROM ".PREFIX."pages_content WHERE lang='".$InLang."' AND page_id=".$page_id, 1);
			// контент
			echo '<tr><td class="RightTD">Название меню<font color="red">*</font>:</td><td><input type="text" name="CONTENT[MENU_TITLE]" value="'.$data['MENU_TITLE'].'" class="field" style="width: 100%;" maxlength="255"/></td></tr>';
			echo '<tr><td class="RightTD">Заголовок (TITLE) страницы:</td><td><input type="text" name="CONTENT[PAGE_TITLE]" value="'.$data['PAGE_TITLE'].'" class="field" style="width: 100%;" maxlength="255"/></td></tr>';
			echo '<tr><td class="RightTD">Заголовок (шапка) страницы:</td><td><input type="text" name="CONTENT[HEADER_TITLE]" value="'.$data['HEADER_TITLE'].'" class="field" style="width: 100%;" maxlength="255"/></td></tr>';
			echo '<tr><td class="RightTD">Текст страницы:</td><td></td></tr><tr><td colspan="2">'.$admin->htmlarea("CONTENT[PAGE_CONTENT]",$data['PAGE_CONTENT'],500,'Pages').'</td></tr>';
			echo '<tr><td class="RightTD" style="padding-top: 15px;"><b>Мета-теги</b></td><td></td></tr>';
			echo '<tr><td class="RightTD">Описание страницы:</td><td><textarea name="CONTENT[PAGE_DESC]" rows="5" cols="30" class="field" style="width: 100%; height: auto; background-image: none;">'.$data['PAGE_DESC'].'</textarea></td></tr>';
			echo '<tr><td class="RightTD">Ключевые слова:</td><td><textarea name="CONTENT[PAGE_KEYS]" rows="5" cols="30" class="field" style="width: 100%; height: auto; background-image: none;">'.$data['PAGE_KEYS'].'</textarea></td></tr>';
			echo '<tr><td class="RightTD" style="padding-top: 15px;"><b>Быстрые ссылки</b></td><td></td></tr>';
			echo '<tr><td class="RightTD">Ссылки:</td><td>';
			?>
			<script language="javascript" type="text/javascript">
			function LinkAdd()
			{
				ret=window.showModalDialog('links.php', '', 'dialogWidth:60;dialogHeight:40;center:yes');
				if(ret == null) return;
				obj = eval("document.getElementById('link_"+ret[0]+"')");
				if(obj == null) {  	
					if(ret[1] != "") {
						document.getElementById("OtherLinks").innerHTML += '<div id="link_' + ret[0] + '">' + ret[1] + ' <a href="javascript: void(0);" style="color: red;" title="Удалить" OnClick=\'javascript: delink("' + ret[0] + '");\'>x</a><input type="hidden" name="link[]" value="' + ret[0] + '"/></div>';
					} 
				}
				return false;
			}
			function delink(lid) 
			{
				document.getElementById("link_"+lid).innerHTML = '';
			}				
			</script>
			<?
			echo '<div id="OtherLinks">';
			$links = $db->sql("SELECT * FROM ".PREFIX."pages_links WHERE page_id = ".$page_id, 2);
			foreach($links as $lnk) {
				$n = $db->sql("SELECT menu_title title FROM ".PREFIX."pages_content WHERE page_id = ".$lnk['LINK_ID'], 1);
				echo '<div id="link_'.$lnk['LINK_ID'].'">'.$n['TITLE'].'
						<a href="javascript: void(0);" style="color: red;" title="Удалить" OnClick="javascript: delink(\''.$lnk['LINK_ID'].'\');">x</a>
						<input type="hidden" name="link[]" value="'.$lnk['LINK_ID'].'"/></div>';
							
			}
			echo '</div>';
			echo '</td></tr>';
			echo '<tr><td class="RightTD"></td><td><a href="javascript: void(0);" onClick="return LinkAdd();">Добавить ссылку</a></td></tr>';
		}
		echo '<tr><td class="RightTD"></td><td><input type="hidden" name="InLang" value="'.$InLang.'"/><input type="hidden" name="refurl" value="'.$_REQUEST['refurl'].'"/><input type="hidden" name="SaveThis" value="true"/><input type="hidden" name="PAGE[PAGE_ID]" value="'.$page_id.'"/><input type="submit" value="Сохранить" class="btn btn-primary" '.(!$admin->access('edit') ? 'disabled' : '').'/></td></tr>';
		echo '</table></form>';
		
		echo $admin->msg_info('<li><b>Псевдоним страницы</b> - название виртуального каталога. Оно должно содержать только латинские строчные буквы. Вместо пробела можно использовать знак _.</li><li><b>Привязка каталога</b> - каталог можно привязать не создавая отдельной страницы</li><li><b>Прежде, чем сменить текущий язык содержания, сохраните сделанные изменения!!!</b></li>');
	}
	
	/* Список каталогов */
	function _list_catalogues($current)
	{
		global $db;
		$rst = $db->sql("SELECT p.page_id, c.menu_title FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND c.lang = '".DEFAULT_LANG."' AND p.page_type = 'catalogue' AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY p.page_order", 2);
		foreach($rst as $r) {
			if($r['PAGE_ID'] == $current) $sel = 'selected'; else $sel = '';
			$txt[] = '<option value="'.$r['PAGE_ID'].'" '.$sel.'>'.$r['MENU_TITLE'].'</option>';
		}
		return @implode('',$txt);
	}
	
	/* Список родительских страниц */
	function _move_pages($pid, $count, $block) 
	{
		global $db, $mvt;
		$count++;
		$pages=$db->sql("SELECT p.page_id, p.page_parent, c.menu_title, p.page_path FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_parent = ".$pid." AND c.lang = '".$_REQUEST['InLang']."' AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_id", 2);
		for($i=0;$i<count($pages);$i++) 
		{
			if($pages[$i]['PAGE_PARENT'] == $block) break;
			if($pages[$i]['PAGE_ID'] == $block) continue;
			$mvt.="<option value=\"".$pages[$i]['PAGE_ID']."\">".str_repeat("&nbsp;",($count*5)).$pages[$i]['MENU_TITLE']."</option>";
			$this->_move_pages($pages[$i]['PAGE_ID'], $count, $block);
		}
		$count--;
		return $mvt;
	}

	/* Список разделов статей, нах */
	function _DocList($cat)
	{
		global $db;
		$res = $db->sql("SELECT * FROM ".PREFIX."docs_cats WHERE site_id = ".$_SESSION['SITE_ID']." AND lang = '".SITELANG."' ORDER BY binary(title)", 2);
		foreach($res as $r) {
			if($cat == $r['ID']) $sel = 'selected'; else $sel = '';
			$txt .= '<option value="'.$r['ID'].'" '.$sel.'>'.$r['TITLE'].'</option>';
		}
		return $txt;
	}

	/* Список разделов фотогалереи, нах */
	function _PhotoList($cat, $parent = 0)
	{
		global $db, $ct;
		if(!$ct) $ct = 0;
		$res = $db->sql("SELECT * FROM ".PREFIX."photo_objects WHERE site_id = ".$_SESSION['SITE_ID']." AND lang = '".SITELANG."' AND parent_id = '".$parent."' AND type = 'gallery' ORDER BY sort_id", 2);
		foreach($res as $r) {
			if($cat == $r['OID']) $sel = 'selected'; else $sel = '';
			$txt .= '<option value="'.$r['OID'].'" '.$sel.'>'.str_repeat('&nbsp;',$ct*2).$r['NAME'].'</option>';
			$ct++;
			$txt .= $this->_PhotoList($cat, $r['OID']);
		}
		$ct--;
		return $txt;
	}

	/* Список имеющихся страниц */
	function _list_pages($pid, $count, $cur_parent, $only_str = false, $redir_to = NULL) {
		global $db, $text;
		if($only_str) {
			if($pid == 0) return "Это главная страница";
			$pages=$db->sql("SELECT p.page_id, p.page_parent, c.menu_title FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_id = ".$pid." AND c.lang = '".DEFAULT_LANG."' AND p.site_id = ".$_SESSION['SITE_ID'], 1);
			return $pages['MENU_TITLE'];
		}
		$count++;
		$pages=$db->sql("SELECT p.page_id, p.page_parent, c.menu_title, p.page_path, p.page_order FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND p.page_parent = ".$pid." AND c.lang = '".DEFAULT_LANG."' AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_order", 2);
		for($i=0;$i<count($pages);$i++) 
		{
			if($redir_to == $pages[$i]['PAGE_ID']) $sel = "selected"; 
			else {
				if(($pages[$i]['PAGE_PATH'] == $cur_parent) || ($pages[$i]['PAGE_ID'] == $cur_parent)) $sel = "selected"; else $sel = "";
			}
			$text.="<option value=\"".$pages[$i]['PAGE_ID']."\" ".$sel.">".str_repeat("&nbsp;",($count*5)).$pages[$i]['MENU_TITLE']."</option>";
			$this->_list_pages($pages[$i]['PAGE_ID'], $count, $cur_parent, $only_str, $redir_to);
		}
		$count--;
		return $text;
	}

	/* Список типов страниц */
	function _list_types($cur_type, $only_str = false)
	{
		global $db, $admin;
		// include aviable types of pages
		$res = $db->sql("SELECT prefix, name FROM ".PREFIX."modules WHERE `show` = 1 ORDER BY binary(name)", 2);
		$text = '<option value="simple">Простой текст</option>';
		foreach($res as $r) {
			if($cur_type == $r['PREFIX']) {
				$sel = "selected"; 
				if($only_str) return $r['NAME'];					
			} else $sel = "";
			$text .= '<option value="'.$r['PREFIX'].'" '.$sel.'>'.$r['NAME'].'</option>';
		}
		return $text;
	}
	
	/* Список шаблонов */
	function _list_templates($cur_template, $only_str = false)
	{
        $handle = opendir(CMS_ROOT_DIR . "/webalife/templates/" . $_SESSION['SITE_THEME']);
		while (false !== ($file = readdir($handle))) {
				if($file=='.' || $file=='..') continue;
				if(eregi('page\\.([a-zA-Z0-9])+'.'\\.tpl', $file)) {
					$tpl[] = $file;
				}
		}
		closedir($handle);
		sort($tpl);
		foreach($tpl as $file) {
			if($cur_template == substr($file,5,strlen($file)-9)) {
				$sel = "selected"; 
			} else $sel = "";
			$tmp.="<option value=\"".substr($file,5,strlen($file)-9)."\" ".$sel.">".$_SESSION['SITE_THEME'].' | '.$file."</option>";
		}
		return $tmp;
	}
	
	/* Определение родительского пути */
	function _realpath($parent, $real_path = null)
	{
		global $real_path, $db;
		if($parent == 0) return $real_path;
		$r=$db->sql("SELECT page_parent, page_path FROM ".PREFIX."pages WHERE page_id = ".$parent, 1);
		if(count($r)>0) {
				$reals=$real_path.$r['PAGE_PATH'];
				$real_path=$this->_realpath($r['PAGE_PARENT'],$reals);
		}
		return $reals;
	}
	
	/* Удаление страниц сайта */
	function KillPages($pid)
	{
		global $db, $Pages2Delete;
		$Pages2Delete = array();
		$Pages2Delete[] = $pid;
		$this->_BuildPageID($pid);
		if(count($Pages2Delete) > 0) {
			// удаляем в соответствии с типом страницы
			foreach($Pages2Delete as $page) {
				$type = $db->sql("SELECT page_type FROM ".PREFIX."pages WHERE page_id = ".$page, 1);
				if($type['PAGE_TYPE'] == "catalogue") $this->KillCatalogue($page); // удаляем каталог
			}
			$strSQL = "DELETE FROM ".PREFIX."pages WHERE page_id in(".implode(", ",$Pages2Delete).")";
			$db->sql($strSQL);
			$strSQL = "DELETE FROM ".PREFIX."pages_content WHERE page_id in(".implode(", ",$Pages2Delete).")";
			$db->sql($strSQL);
			$strSQL = "DELETE FROM ".PREFIX."pages_links WHERE page_id in(".implode(", ",$Pages2Delete).")";
			$db->sql($strSQL);
			$strSQL = "DELETE FROM ".PREFIX."pages_links WHERE link_id in(".implode(", ",$Pages2Delete).")";
			$db->sql($strSQL);
		}
	}

	/* Скрытие страниц сайта */
	function HidePages($pid)
	{
		global $db, $Pages2Delete;
		$Pages2Delete = array();
		$Pages2Delete[] = $pid;
		$this->_BuildPageID($pid);
		if(count($Pages2Delete) > 0) {
			$db->sql("UPDATE ".PREFIX."pages SET page_active = 0 WHERE page_id in(".implode(", ",$Pages2Delete).")");
		}
	}

	/* Показ страниц сайта */
	function ShowPages($pid)
	{
		global $db, $Pages2Delete;
		$Pages2Delete = array();
		$Pages2Delete[] = $pid;
		$this->_BuildPageID($pid);
		if(count($Pages2Delete) > 0) {
			$db->sql("UPDATE ".PREFIX."pages SET page_active = 1 WHERE page_id in(".implode(", ",$Pages2Delete).")");
		}
	}
	
	/* Удаление каталога */
	function KillCatalogue($cid)
	{
		global $db;
		$f = $db->sql("SELECT o.image_value, o.file_value FROM ".PREFIX."catalogue_objects o LEFT JOIN ".PREFIX."catalogue_goods g ON (g.good_id = o.good_id) WHERE g.catalogue_id = ".$cid, 2);
		foreach($f as $f) {
			if($f['IMAGE_VALUE']) @unlink($_SERVER['CMS_IMAGES'].'catalogue/'.$cid."/".$f['IMAGE_VALUE']);
			if($f['FILE_VALUE']) @unlink($_SERVER['CMS_IMAGES'].'catalogue/'.$cid."/".$f['FILE_VALUE']);
		}
		// kill catalogue dir
		@rmdir($_SERVER['CMS_CATALOGUE'].$cid);
		// needle goods id
		$good = $db->sql("SELECT good_id id FROM ".PREFIX."catalogue_goods WHERE catalogue_id = ".$cid, 2);
		// delete main options
		$db->sql("DELETE FROM ".PREFIX."catalogue WHERE page_id = ".$cid);
		// delete link
		$db->sql("DELETE FROM ".PREFIX."catalogue_structures_links WHERE catalogue_id = ".$cid);
		// delete goods
		$db->sql("DELETE FROM ".PREFIX."catalogue_goods WHERE catalogue_id = ".$cid);
		// delete objects
		foreach($good as $g) $db->sql("DELETE FROM ".PREFIX."catalogue_objects WHERE good_id = ".$g['ID']);
	}
	
	/* Построение id страниц, необходимых для удаления */
	function _BuildPageID($pid)
	{
		global $db, $Pages2Delete;
		$pages=$db->sql("SELECT page_id FROM ".PREFIX."pages WHERE page_parent = ".$pid, 2);
		foreach($pages as $p) {
			$Pages2Delete[] = $p['PAGE_ID'];
			$this->_BuildPageID($p['PAGE_ID']);
		}
	}

	/* Замена всех урлов дочерних страниц */
	function ChangePages($pid, $path, $ppath) 
	{
		global $db;
		$pages=$db->sql("SELECT page_id, page_path FROM ".PREFIX."pages WHERE page_parent = ".$pid." AND site_id = ".$_SESSION['SITE_ID'], 2);
		foreach($pages as $p) {
			$db->sql("UPDATE ".PREFIX."pages SET page_path = '".ereg_replace('^'.$ppath, $path, $p['PAGE_PATH'])."' WHERE page_id = ".$p['PAGE_ID']);
			$this->ChangePages($p['PAGE_ID'], $path, $ppath);
		}
	}
}

global $SLANG;
$admin=new admin('PageManager');
$admin->WorkSpaceTitle = $SLANG['Page Header'];

/* Сохранение данных страницы */
if($_POST['SaveThis'] == "true") {
	if($admin->access('edit')) {
		foreach($_POST['PAGE'] as $key => $value) $$key=$value;
		if($PAGE_ID == 0 && $PAGE_PATH == "") $err .= "<li>Укажите псевдоним страницы!</li>";
		if($PAGE_ID == 0 && $PAGE_PATH != "" && !$admin->is_latin($PAGE_PATH)) $err .= "<li>Псевдоним страницы может содержать только латинские буквы, цифры и знак _!</li>";
		if($err) {
			define("ERROR_INFO",$admin->Err_INFO($err));
		} else {
			$h = new handler();
			$CAT_LINK = 0;
			if($PAGE_ID == 0) { // добавление новой страницы
				if($ABS_REDIR) $REDIR_TO = $ABS_REDIR;
				$PAGE_PATH = trim($PAGE_PATH);
				if($PAGE_PATH != '/') {
					$PAGE_PATH = $h->_realpath($PAGE_PARENT).$PAGE_PATH."/";
				}
				if(!$PAGE_PARENT) $PAGE_PARENT = '0';
				$cnt = $db->sql("SELECT count(*) cnt FROM ".PREFIX."pages WHERE page_path = '".$PAGE_PATH."' AND site_id = ".$_SESSION['SITE_ID'], 1);
				if($cnt['CNT'] > 0) define("ERROR_INFO",$admin->Err_INFO("<li>Данный каталог уже существует, введите другое название!</li>"));
				if(strlen(ERROR_INFO) == 10) { // если такого каталога пока не было
					// Считываем порядок страницы в этом разделе
					$res = $db->sql("SELECT max(page_order) ord FROM ".PREFIX."pages WHERE page_parent = ".$PAGE_PARENT." AND site_id = ".$_SESSION['SITE_ID'], 1);
					$ORDER = intval($res['ORD']) + 1;
					$strSQL = "INSERT INTO ".PREFIX."pages SET page_order = ".$ORDER.", page_parent = ".$PAGE_PARENT.", page_path = '".$PAGE_PATH."', inbot = ".$INBOT.", page_active = ".$PAGE_ACTIVE.", page_type = '".$PAGE_TYPE."', page_addit = 0, page_tmplt = '".$PAGE_TMPLT."', redir_to = '".$REDIR_TO."', page_print = ".$PAGE_PRINT.", site_id = ".$_SESSION['SITE_ID'];
					$db->sql($strSQL);
					$PAGE_ID = $db->GetLastID();
					$db->sql("INSERT INTO ".PREFIX."pages_content SET page_id = ".$PAGE_ID.", lang = '".DEFAULT_LANG."', menu_title = 'Unititled'");
					header("Location: ?page_id=".$PAGE_ID."&result=addOK&refurl=".$_REQUEST['refurl'].($_REQUEST['pp'] > 0 ? '&pp='.$_REQUEST['pp'] : ''));
				}
			} else {
				foreach($_POST['CONTENT'] as $key=>$value) $$key=$value;
				$PAGE_CONTENT = addslashes($PAGE_CONTENT);
				if($ABS_REDIR) $REDIR_TO = $ABS_REDIR;
				// обновление главного содержания
				$db->sql("UPDATE ".PREFIX."pages SET page_active = ".$PAGE_ACTIVE.", inbot = ".$INBOT.", page_addit = 0, redir_to = '".$REDIR_TO."', page_print = ".$PAGE_PRINT.", page_type = '".$PAGE_TYPE."', page_tmplt = '".$PAGE_TMPLT."'  WHERE page_id = ".$PAGE_ID);
				// обновление языкового содержания
				$strSQL = "menu_title = '".$admin->my_str_replace($MENU_TITLE)."', page_title = '".$admin->my_str_replace($PAGE_TITLE)."', header_title = '".$admin->my_str_replace($HEADER_TITLE)."', page_desc = '".$admin->my_str_replace($PAGE_DESC)."', page_keys = '".$admin->my_str_replace($PAGE_KEYS)."', page_content = '".$PAGE_CONTENT."'"; //, doc_id = ".$DOC_ID.", photo_id = ".$PHOTO_ID;
				$cnt = $db->sql("SELECT count(*) cnt FROM ".PREFIX."pages_content WHERE lang = '".$_REQUEST['InLang']."' AND page_id = ".$PAGE_ID,1);
				if($cnt['CNT'] == 0) 
					mysql_query("INSERT INTO ".PREFIX."pages_content SET lang = '".$_REQUEST['InLang']."', page_id = ".$PAGE_ID.", ".$strSQL);
				else
					mysql_query("UPDATE ".PREFIX."pages_content SET ".$strSQL." WHERE lang = '".$_REQUEST['InLang']."' AND page_id = ".$PAGE_ID);

				// сохранение быстрых ссылок
				$db->sql("DELETE FROM ".PREFIX."pages_links WHERE page_id = ".$PAGE_ID);
                if (sizeof($_POST['link']) > 0) {
                    foreach($_POST['link'] as $l) {
                        $foo = split(';',$l);
                        $db->sql("INSERT INTO ".PREFIX."pages_links SET page_id = ".$PAGE_ID.", link_id = ".$foo[0]);
                    }
                }
				
				if($MOVE_TO != "") {
					// вытащим название будущей страницы, чтобы добавить урл
					$fut = $db->sql("SELECT page_path path FROM ".PREFIX."pages WHERE page_id = ".$MOVE_TO, 1);
					// вытащим название родительской страницы, чтобы его потом вырезать из всех урлов дочерних страниц
					$par = $db->sql("SELECT page_path path FROM ".PREFIX."pages WHERE page_id = ".$PAGE_PARENT, 1);
					// правим будущий урл
					$PAGE_FUTURE = $fut['PATH'].ereg_replace('^'.$par['PATH'],'',$PAGE_PATH);
					// теперь нужно заправить все урлы дочерних страниц
					$h->ChangePages($PAGE_ID, $PAGE_FUTURE, $PAGE_PATH);
					// теперь все, обновим 
					$db->sql("UPDATE ".PREFIX."pages SET page_parent = ".$MOVE_TO.", page_path = '".$PAGE_FUTURE."' WHERE page_id = ".$PAGE_ID);
				}

				// отсылаем письмо администратору
				$msg = "Внимание!<br/><br/>Страница &laquo;".$MENU_TITLE."&raquo; (<a href=\"http://".$_SERVER['HTTP_HOST'].$admin->PrepareURL($PAGE_PATH)."\">http://".$_SERVER['HTTP_HOST'].$admin->PrepareURL($PAGE_PATH)."</a>) была создана или изменена ".date('d.m.Y в H:i')."<br/><br/>Пользователь: ".$_SESSION['user']."<br/><br/>Странице был присвоен статус: ".($PAGE_ACTIVE == 1 ? 'Активна' : 'Заблокирована к показу')."<br/><br/>------<br/><br/>Данное письмо было отправлено автоматически, отвечать на него не нужно!";
				//mail($_SESSION['CMS_ADMIN_EMAIL'], 'Контроль за публикацией информации на сайте наукоград Российской Федерации город Петергоф', $msg, "From: site@naukograd-peterhof.ru\r\nContent-type: text/html; charset=windows-1251");

				if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
				else header("Location: ?page_id=".$PAGE_ID."&InLang=".$_REQUEST['InLang']."&result=updateOK".($_REQUEST['pp'] > 0 ? '&pp='.$_REQUEST['pp'] : ''));
			}
		}
	}
}

/* Удаление страниц(ы) */
if($_REQUEST['action'] == "delete" && $_REQUEST['page_id'] != "") {
	if($_REQUEST['page_id'] == 1) define("ERROR_INFO", $admin->Err_INFO('<li>Главную страницу сайта удалить нельзя!</li>'));
	else { 
		setcookie('CTree','',time()-3600,'/');
		// удаляем страницы сайта рекурсивно
		$h = new handler();
		$h->KillPages($_REQUEST['page_id']);
		header("Location: ?path=".$_REQUEST['subpath']."&result=deleteOK".($_REQUEST['pp'] > 0 ? '&pp='.$_REQUEST['pp'] : ''));
	}
	unset($_REQUEST['page_id']);
}
		
/* Поднимаем или опускаем страницу */
if($_GET['sort']) {
	if($admin->access('edit')) {
		// вытащим текущую сортировку
		$s = $db->sql("SELECT page_order sid FROM ".PREFIX."pages WHERE page_id = ".$_REQUEST['page_id'], 1);
		switch($_REQUEST['sort']) {
			case "up":
				$sort = $s['SID'] - 1;
				$db->sql("UPDATE ".PREFIX."pages SET page_order = ".$s['SID']." WHERE page_order = ".($sort)." AND site_id = '".$_SESSION['SITE_ID']."'");
				break;
			case "down":
				$sort = $s['SID'] + 1;
				$db->sql("UPDATE ".PREFIX."pages SET page_order = ".$s['SID']." WHERE page_order = ".($sort)." AND site_id = '".$_SESSION['SITE_ID']."'");
				break;
		}
		$db->sql("UPDATE ".PREFIX."pages SET page_order = ".$sort." WHERE page_id = ".$_REQUEST['page_id']." AND site_id = '".$_SESSION['SITE_ID']."'");
		if($_REQUEST['refurl']) header("Location: ".$_REQUEST['refurl']);
		else header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
}

/* Удаление группы страниц */
if($_POST['PageAction']) {
	if($admin->access('kill')) {
		if(!is_array($_POST['SELMENU'])) {
			header("Location: ?result=NoSelect");
			exit;
		} else {
			setcookie('CTree','',time()-3600,'/');
			// удаляем страницы сайта рекурсивно
			$h = new handler();
			switch($_POST['PageAction']) {
				case "delete":
					foreach($_POST['SELMENU'] as $mid) {
						$h->KillPages($mid);
					}
					header("Location: ?path=".$_REQUEST['subpath']."&result=deleteOK".($_REQUEST['pp'] > 0 ? '&pp='.$_REQUEST['pp'] : ''));
					exit;
					break;
				case "hide":
					foreach($_POST['SELMENU'] as $mid) {
						$h->HidePages($mid);
					}
					header("Location: ".$_SERVER['PHP_SELF']);
					exit;
					break;
				case "show":
					foreach($_POST['SELMENU'] as $mid) {
						$h->ShowPages($mid);
					}
					header("Location: ".$_SERVER['PHP_SELF']);
					exit;
					break;
			}
		}
	}
}

$admin->main();

?>