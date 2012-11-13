<table class="table table-bordered">
    <thead>
    <tr>
    {section name=i loop=$header}
        {if $header[i]|count > 1}
            <th width="{$header[i][1]}">{$header[i][0]}</th>
        {else}
            <th>{$header[i]}</th>
        {/if}
    {/section}
        <th width="10%">Действия</th>
    </tr>
    </thead>
    <tbody>
    {section name=i loop=$rows}
        <tr>
            {foreach from=$rows[i] item=item key=key}
            <td>
                {if $child_col eq $key && $child_id neq ""}
                    <a href="?{$child_id}={$rows[i].ID}{$additional_url}">{$item}</a>
                {else}
                    {$item}
                {/if}
            </td>
            {/foreach}
            <td>
                <div class="btn-group">
                    {if !$do_not_sort}
                    <a class="btn btn-mini {if $smarty.section.i.first}disabled{/if}" href="?{$title_id}={$rows[i].ID}{$additional_url}&sort=up" {if $smarty.section.i.first}onclick="return false;"{/if}>
                        <span class="icon-arrow-up" title="Поднять наверх"></span>
                    </a>
                    <a class="btn btn-mini {if $smarty.section.i.last}disabled{/if}" href="?{$title_id}={$rows[i].ID}{$additional_url}&sort=down" {if $smarty.section.i.last}onclick="return false;"{/if}>
                        <span class="icon-arrow-down" title="Опустить вниз"></span>
                    </a>
                    {/if}
                    <a class="btn btn-mini btn-primary" href="?{$title_id}={$rows[i].ID}{$additional_url}">
                        <span class="icon-edit icon-white" title="Редактировать"></span>
                    </a>
                    <a class="btn btn-mini btn-danger {if $delete_prohibited}disabled{/if}" href="?delete={$rows[i].ID}{$additional_url}&reason={$reason_to_delete}" {if $delete_prohibited}onclick="return false;"{else}onclick="return deleteConfirm();"{/if}>
                        <span class="icon-remove icon-white" title="Удалить"></span>
                    </a>
                </div>
            </td>
        </tr>
    {sectionelse}
        <tr>
            <td colspan="{$header|count+1}">Записей не найдено</td>
        </tr>
    {/section}
    </tbody>
</table>
