<link href="/files/css/dtree.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="/files/js/dtree.js"></script>

{literal}
<script language="javascript" type="text/javascript">
    function selAction(vals) {
        if (vals != "") {
            if (vals == "delete") {
                if (confirm('Вы действительно хотите удалить выбранные страницы?')) {
                    document.FormMain.PageAction.value = vals;
                    document.FormMain.submit();
                } else {
                    document.getElementById("action").selectedIndex = 0;
                }
            } else {
                document.FormMain.PageAction.value = vals;
                document.FormMain.submit();
            }
        }
    }

    function setNodeActions() {
        $('a[nodeid]').click(function (e) {
            var id = $(e.target).attr("nodeid");
            if (id === undefined) {
                id = $(e.target).parent().attr("nodeid");
            }
            var r = $.cookie('CTree');
            if (r != null && r != '') {
                $('#d' + r).css('display',  'none');
            }
            var a = $('#d' + id);
            if (r == id) {
                a.css('display', 'none');
                id = '';
            } else {
                a.css('left', e.pageX);
                a.css('display', 'block');
            }
            $.cookie('CTree', id, {
                path:'/'
            });
            return false;
        });
    }
</script>
{/literal}

{if $smarty.get.page_id eq ""}
<div class="btn-toolbar">
    <div class="btn-group">
        <button class="btn" onClick="document.location='?page_id=0';">Новая страница</button>
        <button class="btn" onClick="a.openAll();">Развернуть все</button>
        <button class="btn" onClick="a.closeAll();">Свернуть все</button>
    </div>
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            Действие
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a href="#" onclick="selAction('hide');">Скрыть</a></li>
            <li><a href="#" onclick="selAction('show');">Показать</a></li>
            <li><a href="#" onclick="selAction('delete');">Удалить</a></li>
        </ul>
    </div>
</div>
{else}
<div class="btn-toolbar">
    <div class="btn-group">
        <a href="index.php" class="btn">&laquo; к списку страниц</a>
        {if $smarty.get.page_id > 0}
            <a href="?page_id=0&pid={$smarty.get.page_id}" class="btn">Создать страницу в данном разделе</a>
            <a href="?page_id=0&pid={$data.PAGE_PARENT}" class="btn">Создать страницу в родительском разделе</a>
        {/if}
    </div>
</div>
{/if}