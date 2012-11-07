{* Block news *}

{if $smarty.session.REQUEST_PARAMS.0 eq "12312312312313123123"}

    {MKS_Module_News mode="all"}
<div style="height: 20px;"></div>
<table cellspacing="0" cellpadding="3" width="80%" align="center">
    {section name=i loop=$Feeds}

        <tr>
            <td valign="top" align="center" width="30%">
                {if $Feeds[i].IMAGE}
                    <a href="/showpic.php?picname=images/mks/news/{$Feeds[i].IMAGE}"
                       onClick="javascript: ShowPopUp(this.href + '&mode=popup',150,150); return false;"><img
                            src="/thumbnails.php?picname=images/mks/news/{$Feeds[i].IMAGE}&w=120" alt=""
                            border="0"/></a>
                {/if}
            </td>
            <td valign="top">
                <div style="font-weight: bold; margin-bottom: 10px;">{$Feeds[i].TITLE}</div>
                <div style="margin-bottom: 10px;">{$Feeds[i].ABOUT}</div>
                <div style="margin-bottom: 20px;"><a href="{$_URL_}.{$Feeds[i].ID}.html">Читать подробнее &raquo;</a>
                </div>
            </td>
        </tr>

        <tr style="height: 20px;">
            <td colspan="2"></td>
        </tr>

    {/section}
</table>

{/if}

{if $smarty.session.REQUEST_PARAMS.1 eq ""}

    {MKS_Module_News mode="feed" feed_id=$smarty.session.REQUEST_PARAMS.0}

    {section name=i loop=$Feeds start=$min max=$max}

    <div class="news-item">
        <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Feeds[i].Title}</div>
        <div style="color: #909090; font-size: 11px;">{$Feeds[i].Date}</div>
        <div style="color: #2b2b2b; padding-top: 3px; text-align: justify;">{$Feeds[i].Text}</div>
        <div class="news-item-more"><a href="/news.{$Feeds[i].FEED_ID},{$Feeds[i].ID}.html">подробнее</a> &raquo;</div>
    </div>

    {/section}

{/if}


{if $smarty.session.REQUEST_PARAMS.0 neq "" && $smarty.session.REQUEST_PARAMS.1 neq ""}

    {MKS_Module_News mode="news" feed_id=$smarty.session.REQUEST_PARAMS.0 news_id=$smarty.session.REQUEST_PARAMS.1}

<div class="news-item">
    <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Item.TITLE}</div>
    <div style="color: #909090; font-size: 11px;">{$Item.Date}</div>
    <div style="color: #2b2b2b; padding-top: 3px;">{$Item.CONTENT}</div>
    {if $Item.PHOTO_ID > 0}
        <div style="padding-bottom: 10px;" align="center">
        {include file="photo/gallery.tpl" PHOTO_GALLERY=$Item.PHOTO_ID mode="include"}
        </div>
    {/if}
    <div class="news-item-more">&laquo; <a href="{$_URL_}.{$Item.FEED_ID}.html">вернуться к списку</a></div>
</div>


{/if}
