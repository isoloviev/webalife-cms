<?
require_once CMS_CLASSES."admin.class.php";
$admin = new admin();
?>
<html>
<title>Links Manager</title>
<script><!--

function returnval() {
  arr=new Array();

  arr[0]=window.document.all.tid.value;
  arr[1]=window.document.all.tname.value;
  window.returnValue=arr;
  window.close();
}

function bclosewin() {
  arr=new Array();

  arr[0]='';
  arr[1]='';
  window.returnValue=arr;
  window.close();
}

//--></script>
<link href="<?=$_SERVER['CMS_ADMIN_URL']?>style.css" type="text/css" rel="stylesheet"/>
<meta http-equiv='content-type' content='text/html; charset=windows-1251'>
<body style='margin: 2px; padding: 2px' onload='window.focus()' scrolling='no' scroll='no' onUnload="if (screenTop > 9999) bclosewin()">
  <table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
  <tr height='100%'><td class='cont' valign='top' height='100%' bgcolor='#FFFFFF' height='100%' style='padding: 0px'>

    <table cellspacing='3' cellpadding='0' border='0' width='100%' height='100%'>
		<tr><td valign="top">Страницы сайта:</td></tr>
      	<tr><td height='100%' colspan='4'>
			<iframe name='filebrowser' id='filebrowser' width='100%' height='100%' border='0' frameborder='0' scrolling='yes' scroll='yes' style='border: 1px solid #C0C0C0' src='links_browser.php'></iframe>
		</td></tr>
      	<tr>
        	<td width='100%'>
          	<input type="hidden" name="tid" id="tid" value="">
          	<input type="text" class="field" name="tname" id="tname" style="width: 100%; height: 18px;" readonly>
        	</td>
        <td width='100'><input type='button' class='submit_main' style='width: 100%' value='Вставить' onclick='returnval()'></td>
        <td width='100'><input type='button' class='submit_main' style='width: 100%' value='Отмена' onclick='bclosewin()'></td>
      </tr>
    </table>

  </td></tr></table>
</body>
</html>