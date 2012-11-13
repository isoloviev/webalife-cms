{* Block news *}

{if $smarty.session.REQUEST_PARAMS.1 eq ""}

    {WL_News mode="feed" feed_id=$NewsFeed}

    {section name=i loop=$Feeds start=$min max=$max}

    <div class="well">
        <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Feeds[i].Title}</div>
        <div style="color: #909090; font-size: 11px;">{$Feeds[i].Date}</div>
        <p>{$Feeds[i].Text}</p>
        <a class="btn btn-small" href="/news.{$Feeds[i].FEED_ID},{$Feeds[i].ID}.html">подробнее &raquo;</a>
    </div>

    {/section}

{/if}


{if $smarty.session.REQUEST_PARAMS.0 neq "" && $smarty.session.REQUEST_PARAMS.1 neq ""}

    {WL_News mode="news" feed_id=$smarty.session.REQUEST_PARAMS.0 news_id=$smarty.session.REQUEST_PARAMS.1}

    <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Item.TITLE}</div>
    <div style="color: #909090; font-size: 11px;">{$Item.Date}</div>
    <p>{$Item.CONTENT}</p>
    {if $Item.PHOTO_ID > 0}
        <div style="padding-bottom: 10px;" align="center">
        {include file="photo/gallery.tpl" PHOTO_GALLERY=$Item.PHOTO_ID mode="include"}
        </div>
    {/if}
    <a class="btn btn-small" href="{$_URL_}.{$Item.FEED_ID}.html">&laquo; вернуться к списку</a>

{/if}
