<?php

use yii\db\Migration;
use mdm\admin\components\Configs;

class m170119_103027_menum_data extends Migration
{
    public function up()
    {
        $menuTable = Configs::instance()->menuTable;
        $this->batchInsert($menuTable,
            ['id', 'name', 'parent', 'route', 'multi_controller', 'icon', 'visible', 'order', 'data'],
            //['id' , 'name','parent','route','multi_controller','icon','visible','order','data'],
            [
                [1, '权限管理', NULL, '/admin/default/index', '', 'fa fa-users', 1, 1, '{"icon": "fa fa-users", "visible": true}'],
                [2, '管理员', 1, '/admin/default/index', '', 'fa fa-id-badge', 1, 1, '{"icon": "fa fa-id-badge", "visible": true}'],
                [3, '用户', 2, '/user/index', '', 'fa fa-user', 1, 2, '{"icon": "fa fa-user", "visible": false}'],
                [4, '管理员用户', 2, '/admin-user/index', '', 'fa fa-user', 1, 3, '{"icon": "fa fa-user", "visible": false}'],
                [5, '权限', 2, '/admin/assignment/index', '', 'fa fa-unlock-alt', 1, 1, '{"icon": "fa fa-unlock-alt", "visible": true}'],
                [6, '路由', 4, '/admin/route/index', '', 'fa fa-bars', 1, 1, '{"icon": "fa fa-bars", "visible": true}'],
                [7, '规则', 4, '/admin/rule/index', '', 'fa fa-tasks', 0, 2, '{"icon": "fa fa-tasks", "visible": true}'],
                [8, '角色', 4, '/admin/role/index', '', 'fa fa-user-secret', 1, 4, '{"icon": "fa fa-user-secret", "visible": true}'],
                [9, '分配', 4, '/admin/assignment/index', '', 'fa fa-unlock', 1, 5, '{"icon": "fa fa-unlock", "visible": true}'],
                [10, '菜单', 4, '/admin/menu/index', '', 'fa fa-server', 1, 6, '{"icon": "fa fa-server", "visible": true}'],
                [11, '资源', 4, '/admin/permission/index', '', 'fa fa-puzzle-piece', 1, 3, '{"icon": "fa fa-puzzle-piece", "visible": true}']
            ]
        );
    }

    public function down()
    {
        echo "m170119_103027_menum_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
