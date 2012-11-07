{* Block news anounce *}
{MKS_Module_News mode="anounce" align=$align}
{section name=i loop=$Feeds}
	<div class="news-item">
        <div style="color: #002d5a; font-weight: bold; text-transform: uppercase;">{$Feeds[i].Title}</div>
        <div style="color: #909090; font-size: 11px;">{$Feeds[i].Date}</div>
		<div style="color: #2b2b2b; padding-top: 3px;">{$Feeds[i].Text}</div>
        <div class="news-item-more"><a href="/news.{$Feeds[i].FEED_ID},{$Feeds[i].ID}.html">подробнее</a> &raquo;</div>
	</div>
{/section}
