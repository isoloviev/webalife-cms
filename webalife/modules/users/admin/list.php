<?php
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
*/

require_once CMS_CLASSES . "admin.class.php";

class handler
{
    function start()
    {
        global $db, $admin, $err;

        if (isset($_REQUEST['uid'])) {
            return $this->view(intval($_REQUEST['uid']));
        }

        ?>
    <div id="users-grid"></div>
    <link rel="stylesheet" type="text/css" href="/files/ext/ext-all.css"/>

    <style type="text/css">
        .newRecordsClass td {
            background-color: #009933;
        }

        .disabledRecordsClass td {
            background-color: #cccccc;
        }
    </style>
    <script language="javascript">
    Ext.onReady(function () {

        // create the Data Store
        var store = new Ext.data.JsonStore({
            root:'users',
            totalProperty:'totalCount',
            idProperty:'ID',
            remoteSort:true,

            fields:[
                'ID', 'NAME', 'LOGIN', 'ISPASSWORD', 'STATUS', 'GROUP_NAME'
            ],

            proxy:new Ext.data.HttpProxy({
                url:'load.php?cmd=viewUsers'
            })
        });

        var pagingBar = new Ext.PagingToolbar({
            pageSize:25,
            store:store,
            displayInfo:true,
            displayMsg:'Показано пользователей {0} - {1} из {2}',
            beforePageText:'Страница',
            afterPageText:'из {0}',
            emptyMsg:'Пользователей не найдено',

            items:['-', {
                id:'btnClearFilter',
                text:'Убрать фильтр',
                disabled:true,
                listeners:{
                    'click':function (btn) {
                        Ext.getCmp('txtSearch').setValue('');
                        Ext.getCmp('btnFind').disable();
                        store.baseParams = {filter:''};
                        store.reload({callback:function () {
                            pagingBar.changePage(1);
                        }});
                        btn.disable();
                    }
                }
            }]
        });

        var ckbxCol = new Ext.grid.CheckboxColumn({
            dataIndex:'XID'
        });

        function renderActions(value, p, r) {
            return '<a href="javascript: setPassword(' + r.data.ID + ');">' + (!value ? 'Установить' : 'Изменить') + '</a>';
        }

        var grid = new Ext.grid.GridPanel({
            el:'users-grid',
            id:'usersGrid',
            width:678,
            height:500,
            store:store,
            trackMouseOver:false,
            loadMask:true,

            // grid columns
            columns:[
                ckbxCol
                , {
                    header:"Наименование / ФИО",
                    dataIndex:'NAME',
                    id:'name',
                    width:120,
                    sortable:true
                }, {
                    header:"E-Mail",
                    dataIndex:'LOGIN',
                    width:100
                }, {
                    header:"Группа",
                    dataIndex:'GROUP_NAME',
                    width:100
                }, {
                    header:"Действия",
                    width:100,
                    renderer:renderActions,
                    align:'center'
                }],

            autoExpand:'name',

            // customize view config
            viewConfig:{
                forceFit:true,
                getRowClass:function (row, index) {
                    var cls = '';
                    if (row.data.STATUS == -1)
                        cls = 'newRecordsClass';
                    else if (row.data.STATUS == 0)
                        cls = 'disabledRecordsClass';
                    return cls;
                }
            },

            listeners:{
                'cellclick':function (grid, rowIndex, columnIndex, e) {
                    if (columnIndex == 0) {
                        var td = grid.getView().getCell(rowIndex, columnIndex);

                        if (!Ext.fly(td).hasClass('x-grid3-check-col-on'))
                            Ext.getCmp('btnGo').enable();
                        else {
                            var ct = grid.getStore().getCount();
                            var rowChecked = 0;

                            for (var i = 0; i < ct; i++) {
                                var tdi = grid.getView().getCell(i, 0);
                                if (Ext.fly(tdi).hasClass('x-grid3-check-col-on'))
                                    rowChecked++;
                            }

                            if (rowChecked <= 1) Ext.getCmp('btnGo').disable();
                        }
                    }
                },

                'headerclick':function (grid, columnIndex, e) {
                    if (columnIndex == 0) {
                        var col = grid.getColumnModel().getColumnById(grid.getColumnModel().getColumnId(columnIndex));
                        if (!col.masterValue)
                            Ext.getCmp('btnGo').enable();
                        else
                            Ext.getCmp('btnGo').disable();
                    }
                },

                'rowdblclick':function (grid, idx) {
                    var r = grid.getStore().getAt(idx);
                    document.location = '?uid=' + r.get('ID');
                }
            },

            plugins:ckbxCol,

            tbar:[
                {
                    id:'btnNew',
                    text:'Новый пользователь',
                    listeners:{
                        click:function () {
                            document.location = "?uid=0";
                        }
                    }
                },
                new Ext.form.ComboBox({
                    id:'cmdAction',
                    store:[
                        'Активировать',
                        'Заблокировать',
                        'Удалить'
                    ],
                    displayField:'cmdAction',
                    typeAhead:true,
                    mode:'local',
                    triggerAction:'all',
                    emptyText:'Выберите действие...',
                    selectOnFocus:true,
                    width:135
                }),
                {
                    id:'btnGo',
                    text:'OK',
                    disabled:true,
                    listeners:{
                        'click':function (btn, e) {
                            fireAction();
                        }
                    }
                },
                '-',
                new Ext.form.TextField({
                    id:'txtSearch',
                    width:200,
                    emptyText:'Введите слово для поиска',
                    listeners:{
                        'change':function (fld, newValue, oldValue) {
                            if (newValue != "") Ext.getCmp('btnFind').enable();
                            else Ext.getCmp('btnFind').disable();
                        }
                    }
                }),
                {
                    id:'btnFind',
                    text:'Найти',
                    disabled:true,
                    listeners:{
                        'click':function () {
                            searchInGrid(Ext.getCmp('txtSearch').getEl().getValue());
                        }
                    }
                },
                '->',
                new Ext.form.Label({
                    text:"На странице:"
                }),
                new Ext.form.NumberField({
                    id:'perPage',
                    value:pagingBar.pageSize,
                    style:'text-align: center',
                    width:30,

                    listeners:{
                        'change':function (fld) {
                            pagingBar.pageSize = fld.getValue();
                            store.load({params:{start:0, limit:fld.getValue()}});
                        }
                    }
                })
            ],

            // paging bar on the bottom
            bbar:pagingBar
        });

        // render it
        grid.render();

        // add baseParams
        store.baseParams = {
            filter:(!Ext.getCmp('btnFind').disabled ? Ext.getCmp('txtSearch').getEl().getValue() : "")
        };

        // trigger the data store load
        store.load({params:{start:0, limit:Ext.getCmp('perPage').getValue()}});
    });

    var userId = null;
    var passwordForm = null;
    var ordersForm = null;

    function setPassword(uid) {
        userId = uid;
        showPasswordForm();
    }

    function showPasswordForm() {
        if (passwordForm != null) {
            passwordForm.show();
            return;
        }

        passwordForm = new Ext.Window({
            labelAlign:'top',
            title:'Пароль доступа',
            plain:true,
            border:false,
            resizable:false,
            width:300,
            autoHeight:true,
            autoScroll:true,
            modal:true,

            items:[
                {
                    xtype:'form',
                    id:'passwordForm',
                    waitMsgTarget:true,
                    bodyStyle:'padding:10px',
                    labelWidth:120,

                    items:[
                        {
                            xtype:'textfield',
                            fieldLabel:'Новый пароль',
                            id:'fieldPassword',
                            name:'PASSWORD',
                            inputType:'password',
                            allowBlank:false
                        },
                        {
                            xtype:'textfield',
                            fieldLabel:'Поворите пароль',
                            id:'fieldRePassword',
                            name:'REPASSWORD',
                            inputType:'password',
                            allowBlank:false
                        }
                    ]
                }
            ],

            buttons:[
                {
                    text:'Сохранить',
                    handler:function () {
                        var form = Ext.getCmp('passwordForm').getForm();
                        if (form.isValid()) {
                            var pass = Ext.getCmp('fieldPassword').getValue();
                            var rePass = Ext.getCmp('fieldRePassword').getValue();

                            if (pass == rePass) {
                                form.submit({url:'load.php?cmd=saveUserPassword&uid=' + userId, waitMsg:'Сохранение данных', success:function () {
                                    passwordForm.hide();
                                    Ext.getCmp('usersGrid').getStore().reload({waitMsg:'Обновление данных'});
                                }});
                            } else {
                                Ext.MessageBox.alert("Ошибка", "Введенные пароли не совпадают");
                            }
                        } else {
                            Ext.MessageBox.alert("Ошибка", "Для продолжения, заполните все поля");
                        }
                    }
                },
                {
                    text:'Отмена',
                    handler:function () {
                        passwordForm.hide();
                    }
                }
            ],

            closeAction:'hide',

            listeners:{
                'activate':function () {
                    Ext.getCmp('passwordForm').getForm().reset();
                }
            }
        });
        passwordForm.show();
    }

    function showOrders(uid) {

        userId = uid;

        // create the Data Store
        var store = new Ext.data.JsonStore({
            root:'orders',
            idProperty:'ORDER_ID',

            fields:[
                'ORDER_ID', 'NAME', 'GCNT', 'STATUS', 'AMOUNT'
            ],

            proxy:new Ext.data.HttpProxy({
                url:'load.php?cmd=getOrders&uid=' + userId
            })
        });

        if (ordersForm != null) {
            Ext.getCmp('orders').getStore().proxy = new Ext.data.HttpProxy({
                url:'load.php?cmd=getOrders&uid=' + userId
            });
            ordersForm.show();
            return;
        }

        function renderStatus(value, p, r) {
            var sel1 = "", sel2 = "", sel3 = "";
            switch (parseInt(value)) {
                case -1:
                    sel1 = "selected";
                    break;
                case 0:
                    sel2 = "selected";
                    break;
                case 1:
                    sel3 = "selected";
                    break;
            }

            return '<select style="width: 100%" onChange="setStatus(this, ' + r.data.ORDER_ID + ')">' +
                '<option value="-1" ' + sel1 + '>В обработке</option>' +
                '<option value="0" ' + sel2 + '>Принят в работу</option>' +
                '<option value="1" ' + sel3 + '>Выполнен</option></select>';
        }

        ordersForm = new Ext.Window({
            title:'Список заказов',
            plain:true,
            border:false,
            resizable:false,
            width:650,
            modal:true,
            autoScroll:true,

            items:[
                {
                    xtype:'grid',
                    id:'orders',
                    store:store,
                    trackMouseOver:false,
                    loadMask:true,
                    height:200,
                    columns:[
                        {
                            header:"Заказ",
                            dataIndex:'NAME',
                            id:'name',
                            width:220,
                            sortable:true
                        },
                        {
                            header:"Количество",
                            dataIndex:'GCNT',
                            width:100,
                            align:'center'
                        },
                        {
                            header:"Сумма",
                            dataIndex:'AMOUNT',
                            width:100,
                            align:'right'
                        },
                        {
                            header:"Статус",
                            dataIndex:'STATUS',
                            width:150,
                            renderer:renderStatus
                        }
                    ],
                    autoExpandColumn:'name'
                }
            ],

            closeAction:'hide',

            listeners:{
                'activate':function () {
                    Ext.getCmp('orders').getStore().reload();
                }
            }
        });
        ordersForm.show();
    }

    function searchInGrid(strSearch) {
        var gridStore = Ext.getCmp('usersGrid').getStore();
        gridStore.baseParams = {
            filter:strSearch
        }
        gridStore.load({params:{start:0, limit:Ext.getCmp('perPage').getValue()}});
        Ext.getCmp('btnClearFilter').enable();
    }

    function setStatus(obj, orderId) {
        var cmd = "setStatus";
        var proxy = new Ext.data.HttpProxy({
            url:"load.php?cmd=" + cmd
        });
        var conn = proxy.getConnection();
        conn.request({url:"load.php?cmd=" + cmd, params:"order_id=" + orderId + "&status=" + obj.value});
    }

    function fireAction() {
        var cmdAction = Ext.getCmp('cmdAction').getValue();

        if (cmdAction == "") {
            Ext.MessageBox.alert("Ошибка", "Выберите действие");
            return;
        }

        var grid = Ext.getCmp('usersGrid');
        var ct = grid.getStore().getCount();
        var selectedIds = [];

        for (var i = 0; i < ct; i++) {
            var tdi = grid.getView().getCell(i, 0);
            if (Ext.fly(tdi).hasClass('x-grid3-check-col-on')) {
                var row = grid.getStore().getAt(i);
                selectedIds[selectedIds.length] = row.data.ID;
            }
        }

        switch (cmdAction) {
            case "Удалить":
                cmd = "actionDelete";
                break;
            case "Заблокировать":
                cmd = "actionDisable";
                break;
            case "Активировать":
                cmd = "actionEnable";
                break;
            default:
                return;
        }

        var proxy = new Ext.data.HttpProxy({
            url:"load.php?cmd=" + cmd
        });

        var strUser = "";
        Ext.each(selectedIds, function (item, idx, arr) {
            strUser += item + (idx < arr.length - 1 ? ";" : "");
        });

        if (cmd == 'actionDelete') {
            Ext.MessageBox.show({
                title:'Подтверждение',
                msg:'Вы уверены что хотите удалить выбранных пользователей?',
                buttons:Ext.MessageBox.YESNO,
                fn:function (btn) {
                    if (btn == 'yes') {
                        proxy.load({userIds:strUser}, new Ext.data.JsonReader({
                            totalProperty:"rows",
                            root:"records",
                            id:"ID"
                        }, [
                            {name:'EMAIL'},
                            {name:'ID'}
                        ]), onFireActionSuccess);
                    }
                },
                icon:Ext.MessageBox.QUESTION
            });
        } else if (cmd == 'actionEnable') {
            Ext.MessageBox.show({
                title:'Подтверждение',
                msg:'Обратите внимание, что у выбранных пользователей изменятся пароли.<br/>Продолжить?',
                buttons:Ext.MessageBox.YESNO,
                fn:function (btn) {
                    if (btn == 'yes') {
                        proxy.load({userIds:strUser}, new Ext.data.JsonReader({
                            totalProperty:"rows",
                            root:"records",
                            id:"ID"
                        }, [
                            {name:'EMAIL'},
                            {name:'ID'}
                        ]), onFireActionSuccess);
                    }
                },
                icon:Ext.MessageBox.QUESTION
            });
        } else {
            proxy.load({userIds:strUser}, new Ext.data.JsonReader({
                totalProperty:"rows",
                root:"records",
                id:"ID"
            }, [
                {name:'EMAIL'},
                {name:'ID'}
            ]), onFireActionSuccess);
        }

        Ext.getCmp('cmdAction').setValue('');
    }

    function onFireActionSuccess(record, args, success) {
        if (success)
            Ext.getCmp('usersGrid').getStore().reload();
        else
            Ext.MessageBox.alert("Ошибка", "Произошла ошибка во время исполнения запроса");
    }
    </script>
    <?php
    }

    function view($uid)
    {
        global $db;
        $data = $db->sql("SELECT * FROM " . PREFIX . "users where id = " . $uid, 1);
        if (sizeof($data) == 0) {
            $data = $_REQUEST;
        }
        echo '<p><a href="list.php">к списку</a></p>';
        if (defined("ERROR_INFO")) echo ERROR_INFO;
        echo '<form action="" method="post" enctype="multipart/form-data"><table cellspacing="0" cellpadding="3" border="0" class="Manage">';
        echo '<tr><td class="RightTD" width="20%">ФИО<font color="red">*</font>:</td><td><input type="text" name="NAME" value="' . $data['NAME'] . '" class="field" style="width: 100%;" maxlength="255"></td></tr>';
        echo '<tr><td class="RightTD">Логин<font color="red">*</font>:</td><td><input type="text" name="LOGIN" value="' . $data['LOGIN'] . '" class="field" style="width: 100%;" maxlength="255"></td></tr>';
        echo '<tr><td class="RightTD">E-Mail<font color="red">*</font>:</td><td><input type="text" name="EMAIL" value="' . $data['EMAIL'] . '" class="field" style="width: 100%;" maxlength="255"></td></tr>';
        echo '<tr><td class="RightTD">Группа:</td><td><select name="GROUP_ID" class="field" style="width: 100%;" ' . ($data['GROUP_ID'] == 1 ? 'disabled="true"' : '') . '>';
        $gr = $db->sql('SELECT * FROM ' . PREFIX . 'users_groups WHERE group_id not in (2,1) ORDER BY group_id', 2);
        foreach ($gr as $g) {
            echo '<option value="' . $g['GROUP_ID'] . '" ' . ($g['GROUP_ID'] == $data['GROUP_ID'] ? 'selected' : '') . '>' . $g['TITLE'] . '</option>';
        }
        echo '</select></td></tr>';
        echo '<tr><td class="RightTD">Статус:</td><td><select name="STATUS" class="field" style="width: 100%;" ' . ($data['GROUP_ID'] == 1 ? 'disabled="true"' : '') . '>';
        echo '<option value="1" ' . (1 == $data['STATUS'] ? 'selected' : '') . '>Активен</option><option value="0"' . (0 == $data['STATUS'] ? 'selected' : '') . '>Заблокирован</option></select></td></tr>';
        echo '<tr><td class="RightTD"></td><td><input type="hidden" name="USER_ID" value="' . $uid . '"><input type="hidden" name="cmd" value="updateUser"><input type="submit" name="SaveObject" value="Сохранить изменения" class="btn btn-primary"></td></tr>';
        echo '</table></form>';
        return "";
    }

}

global $SLANG;
$admin = new admin('USERS');
$admin->WorkSpaceTitle = 'Учетные записи';

if ($_REQUEST['cmd'] == 'updateUser') {
    $isFailed = false;
    if ($_REQUEST['NAME'] == '' || $_REQUEST['LOGIN'] == '' || $_REQUEST['EMAIL'] == '') {
        define("ERROR_INFO", $admin->err_info('<li>Все поля являются обязательными</li>'));
        $isFailed = true;
    }
    // check for unique email
    if (!$isFailed && $_REQUEST['USER_ID'] == 0) {
        $rst = $db->sql("SELECT count(*) cnt FROM " . PREFIX . "users WHERE login = '" . $_REQUEST['LOGIN'] . "'", 1);
        if ($rst['CNT'] != 0) {
            define("ERROR_INFO", $admin->err_info('<li>Данный логин уже имеется в системе</li>'));
            $isFailed = true;
        }
    }
    if (!$isFailed) {
        $d[] = "NAME = '" . $_REQUEST['NAME'] . "'";
        $d[] = "EMAIL = '" . $_REQUEST['EMAIL'] . "'";
        $d[] = "LOGIN = '" . $_REQUEST['LOGIN'] . "'";
        if ($_REQUEST['GROUP_ID'] > 0) {
            $d[] = "GROUP_ID = '" . $_REQUEST['GROUP_ID'] . "'";
            $d[] = "STATUS = '" . $_REQUEST['STATUS'] . "'";
        }
        $doCreate = $_REQUEST['USER_ID'] == 0;
        if ($doCreate) {
            $email = $_REQUEST['LOGIN'];
            $pass = generate_password(8);
            $d['PSWRD'] = md5($pass);
            mail($_REQUEST['EMAIL'], 'Регистрация на сайте ' . $_SERVER['HTTP_HOST'], "Поздравляем, Вы успешно прошли регистрацию.\r\n\r\nДанные для входа:\r\n\r\n\tПользователь: " . $email . "\r\n\tПароль:" . $pass . "\r\n\r\n----\r\n\r\nС Уважением,\r\nАдминистрация сайта", "From: info@" . $_SERVER['HTTP_HOST'] . "\r\nContent-Type: text/plain; charset=utf-8");
        }
        $db->sql(($doCreate ? "INSERT INTO " : "UPDATE ") . PREFIX . "users SET " . implode(", ", $d) . ($doCreate ? "" : " WHERE id = " . $_REQUEST['USER_ID']));
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

function generate_password($number)
{
    $arr = array('a', 'b', 'c', 'd', 'e', 'f',
        'g', 'h', 'i', 'j', 'k', 'l',
        'm', 'n', 'o', 'p', 'r', 's',
        't', 'u', 'v', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F',
        'G', 'H', 'I', 'J', 'K', 'L',
        'M', 'N', 'O', 'P', 'R', 'S',
        'T', 'U', 'V', 'X', 'Y', 'Z',
        '1', '2', '3', '4', '5', '6',
        '7', '8', '9', '0', '.', ',',
        '(', ')', '[', ']', '!', '?',
        '&', '^', '%', '@', '*', '$',
        '<', '>', '/', '|', '+', '-',
        '{', '}', '`', '~');
    // Генерируем пароль
    $pass = "";
    for ($i = 0; $i < $number; $i++) {
        // Вычисляем случайный индекс массива
        $index = rand(0, count($arr) - 1);
        $pass .= $arr[$index];
    }
    return $pass;
}

$admin->main();

?>