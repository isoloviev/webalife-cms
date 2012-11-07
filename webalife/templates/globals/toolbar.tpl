<div class="btn-toolbar">
    <div class="btn-group">
        {section name=i loop=$buttons}
            <a href="{$buttons[i].path}" class="btn">{$buttons[i].title}</a>
        {/section}
    </div>
</div>