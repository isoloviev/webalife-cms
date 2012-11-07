<?
require_once CMS_CLASSES."admin.class.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Links</title>
<link href="<?=$_SERVER['CMS_ADMIN_URL']?>style.css" type="text/css" rel="stylesheet"/>
<script language="javascript1.2" type="text/javascript">
function OnMark(rowID, color)
{
	rowID.style.background = color;
}
function PushIt(id, bid, name)
{
	top.document.getElementById('tid').value = id;
	top.document.getElementById('tname').value = name;
}
</script>
</head>

<body>
<?
$admin = new admin();
echo '<table cellspacing="1" cellpadding="3" border="0" width="100%" style="background-color: #316ac5;"><tr style="background-color: #84C1FF; font-weight: bold; text-align: center;"><td width="100%">Название страницы</td></tr>';
PageTree(0);
echo '</table>';

/* Построение дерева страниц */
function PageTree($parent = 0, $count = -1)
{
	global $db, $admin;
	$count++;
	$menu=$db->sql("SELECT c.menu_title name_menu, c.record_id rid, p.page_path, p.page_parent pid, p.page_id id, p.page_order, p.page_active FROM ".PREFIX."pages p, ".PREFIX."pages_content c WHERE p.page_id = c.page_id AND c.lang = '".SLANG."' AND p.page_parent = ".$parent." AND p.site_id = ".$_SESSION['SITE_ID']." ORDER BY page_order", 2);
	for($i=0;$i<count($menu);$i++) {
		if($menu[$i]['PAGE_ACTIVE'] == "1") $active = "активна"; else $active = "заблокирована";
		print "<tr bgColor=\"#FFFFFF\" onClick=\"javascript:PushIt('".$menu[$i]['ID']."', '0', '".$menu[$i]['NAME_MENU']."');\" onMouseOver=\"javascript: OnMark(this, '#E1F5FF');\" onMouseOut=\"javascript: OnMark(this, '#FFFFFF');\" style=\"height: 30px; cursor: hand;\">";
		print "<td>".str_repeat('&nbsp;', ($count * 5)).($count > 0 ? '<img src="'.$_SERVER['CMS_ROOT_URL'].'images/mks/admin/tree.gif" alt="absmiddle"/>' : '')."<img src=\"".$_SERVER['CMS_ROOT_URL']."images/mks/admin/folder.gif\" alt=\"\" border=\"0\" align=\"absmiddle\"/>&nbsp;";
		//print "<a href=\"page_list.php?path=".$menu[$i]['PAGE_PATH']."\" class=\"MainMenu\">;
		print $menu[$i]['NAME_MENU']."</td></tr>";
		PageTree($menu[$i]['ID'], $count);
	}
	$count--;
}
?>
</body>
</html>
