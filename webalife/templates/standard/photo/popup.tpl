{* Photo gallery :: PopUp *}
{MKS_Module_Photo mode="pop"}
<div align="center"><a href="javascript: window.close();"><img id="PhotoSize" src="{$CMS_ROOT_URL}files/Gallery/{$PoP.PICNAME}" alt="{$PoP.NAME}" border="0" /></a></div>
<div align="center" style="padding: 12px 0px; color: #0b4da2; font-family: Arial, Helvetica, sans-serif;">&larr; {if $PoP.PREV}<a href="{$PoP.PREV}" style="color: #0b4da2;">{/if}предыдущее фото{if $PoP.PREV}</a>{/if}&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;{if $PoP.NEXT}<a href="{$PoP.NEXT}" style="color: #0b4da2;">{/if}следующее фото{if $PoP.NEXT}</a>{/if} &rarr;</div>