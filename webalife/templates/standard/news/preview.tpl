{* Block news anounce *}
{WL_News mode="anounce" align=$align}
{section name=i loop=$Feeds}
	<div class="well">
        <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Feeds[i].Title}</div>
        <div style="color: #909090; font-size: 11px;">{$Feeds[i].Date}</div>
		<p>{$Feeds[i].Text}</p>
        <div class="news-item-more"><a class="btn btn-small" href="/news.{$Feeds[i].FEED_ID},{$Feeds[i].ID}.html">подробнее &raquo;</a></div>
	</div>
{/section}
