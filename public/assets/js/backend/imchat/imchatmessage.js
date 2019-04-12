define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'imchat/imchatmessage/index',
                    add_url: 'imchat/imchatmessage/add',
                    edit_url: 'imchat/imchatmessage/edit',
                    del_url: 'imchat/imchatmessage/del',
                    multi_url: 'imchat/imchatmessage/multi',
                    table: 'imchat_message',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'room.title', title: __('Room.title'), operate:'LIKE'},
                        {field: 'room_id', title: __('room_id'), searchList: {"0": '普通消息房间'}},
                        {field: 'from_uid', operate:false, title: __('From_uid') },
                        {field: 'to_uid', operate:false,title: __('To_uid')},
                        {field: 'content', operate:false,title: __('Content')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});