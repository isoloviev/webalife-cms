{MKS_Module_Photo categories=$PHOTO_GALLERY mode=$mode}

{if $GalleryShow eq "photo"}
	{* Показываем только превьюшки (тип 'p') *}
	{if $GalInfo.PHOTO_TYPE eq "p"}
    {if $mode eq ""}
        <h1>{$GalInfo.NAME}</h1>
        {if $GalInfo.ABOUT}<p>{$GalInfo.ABOUT}</p>{/if}
    {/if}
	<table cellspacing="0" cellpadding="0" border="0" align="center">
	{$PHOTO}
	</table>
	{/if}
	
	
	{* Показываем превьюшки с большой фоткой (тип 'P+p') *}
	{if $GalInfo.PHOTO_TYPE eq "P+p"}
	{literal}
	<script language="javascript1.2" type="text/javascript">
	<!--
	function ShowPic(srcPic, srcUrl, picId) {
		document.getElementById('GalPic').src = srcPic;
		document.getElementById('pid').innerHTML = picId;
		document.getElementById('GalUrl').href = srcUrl;
		return false;
	}
	//-->
	</script>
	{/literal}
	<p><b>{$GalInfo.NAME}</b></p>
	{if $GalInfo.ABOUT}<p>{$GalInfo.ABOUT}</p>{/if}
	{if $OneP.OID}
	<p align="center">
	<table cellspacing="0" cellpadding="3" border="0">
		<tr><td align="center"><div id="pid" style="display: none;"></div><a id="GalUrl" href="/gallery.php?gid={$OneP.PARENT_ID}&photo={$OneP.OID}" onClick="return PopUp('/gallery.php?gid={$OneP.PARENT_ID}&photo=' + document.getElementById('pid').innerHTML,600,480);"><img id="GalPic" src="{$CMS_ROOT_URL}files/Gallery/{$OneP.IMAGE}" border="0" alt="{$OneP.NAME}" /></a></td></tr>
		<tr><td align="center"><table cellspacing="0" cellpadding="3" border="0">
		{$PHOTO}
		</table></td></tr>
	</table>
	</p>
	{/if}
	{/if}
	
	{* Показываем большую фотку (тип 'P') *}
	{if $GalInfo.PHOTO_TYPE eq "P"}
	<p><b>{$GalInfo.NAME}</b></p>
	{if $GalInfo.ABOUT}<p>{$GalInfo.ABOUT}</p>{/if}
	{if $OneP.OID}
	<p align="center">
	<table cellspacing="0" cellpadding="3" border="0">
		<tr><td align="center"><a href="/gallery.php?gid={$OneP.PARENT_ID}&photo={$OneP.OID}" onClick="return PopUp('/gallery.php?gid={$OneP.PARENT_ID}&photo={$OneP.OID}',600,480);"><img src="{$CMS_ROOT_URL}files/Gallery/{$OneP.IMAGE}" border="0" alt="{$OneP.NAME}" /></a></td></tr>
		<tr><td align="center"><img src="/images/mks/arrow_left.gif" alt="{$Lang.PAGER_2}" border="0" style="position: relative; top: 2px;"/> <a href="{$_URL_}.{$OneP.PARENT_ID}.html?pg={$ViewPrev}">{$Lang.PAGER_2}</a>&nbsp;&nbsp;&nbsp;<a href="{$_URL_}.{$OneP.PARENT_ID}.html?pg={$ViewNext}">{$Lang.PAGER_3}</a> <img src="/images/mks/arrow_right.gif" alt="{$Lang.PAGER_3}" border="0" style="position: relative; top: 2px;"/></td></tr>
	</table>
	</p>
	{/if}
	{/if}
	{$PAGER}

{/if}

{if $Galleries || $smarty.get.query neq ""}
	
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td>
		<form action="" method="get" name="search">
		<table cellspacing="0" cellpadding="3">
		<tr>
			<td style="background: url({$CMS_ROOT_URL}images/rc/field.gif) left top no-repeat; width: 280px; padding: 3px 10px;"><input type="text" name="query" value="{$smarty.get.query|default:"введите слово для поиска"}" onClick="this.value=''" style="background: none; border: 0px; color: white; width: 280px;"></td>
			<td><input type="image" src="{$CMS_ROOT_URL}images/rc/find.gif" onClick="document.search.submit();"/></td>
		</tr>
		</table>
		</form>
	</td></tr>
	{section name=i loop=$Galleries}
		<tr><td valign="top" style="padding-bottom: 20px;">
		<table cellspacing="0" cellpadding="3" width="100%">
		<tr>
			<td valign="top" align="center" width="8">
				{if $Galleries[i].IMG}
					<table cellspacing="0" cellpadding="0" border="0" style="margin-right: 10px;"><tr><td><img src="{$CMS_ROOT_URL}images/rc/photo1.gif" alt="" border="0"/></td><td bgcolor="#d0d1d3"></td><td><img src="{$CMS_ROOT_URL}images/rc/photo2.gif" alt="" border="0"/></td></tr>
	  				<tr><td bgcolor="#d0d1d3"></td><td bgcolor="#d0d1d3">
	  					<a href="{$_URL_}.{$Galleries[i].ID}.html" title=""><img src="/thumbnails.php?{$Galleries[i].IMG}" alt="" border="0"/></a>
      				</td><td bgcolor="#d0d1d3"></td></tr>
	  				<tr><td><img src="{$CMS_ROOT_URL}images/rc/photo3.gif" alt="" border="0"/></td><td bgcolor="#d0d1d3"></td><td><img src="{$CMS_ROOT_URL}images/rc/photo4.gif" alt="" border="0"/></td></tr>
	  				</table>								
				{else}
					&nbsp;
				{/if}
			</td>
			<td align="left" valign="top" width="%" style="padding-left: 10px; "><div style="font-weight: bold; font-size: 14px; padding-bottom: 10px;">{$Galleries[i].NAME}</div>{$Galleries[i].ABOUT}<div style="padding: 5px 0px;"></div><a href="{$_URL_}.{$Galleries[i].ID}.html" title="{$Lang.GO_PHOTO}" style="font-size: 11px;">Смотреть</a></td>
		</tr>
		</table>
		</td></tr>
	{sectionelse}
		<tr><td valign="top" style="padding-bottom: 20px; color: white;">
		Слайдов не найдено
		</td></tr>
	{/section}
	</table>
{/if}


{* if $GalleryShow eq "photo"}<div style="color: #0b4da2;">&larr; <a href="{$PHOTO_BACK_URL}" style="font-size: 11px;">к списку</a></div>{/if *}

