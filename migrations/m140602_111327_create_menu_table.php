<?php

use mdm\admin\components\Configs;

/**
 * Migration table of table_menu
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class m140602_111327_create_menu_table extends \yii\db\Migration
{

    /**
     * @inheritdoc
     */
    public function up()
    {
        $menuTable = Configs::instance()->menuTable;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($menuTable, [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull()->comment('菜单'),
            'parent' => $this->integer()->comment('父级'),
            'route' => $this->string()->comment('路由'),
            'multi_controller' => $this->string()->comment('多控制器'),
            'icon' => $this->string()->comment('图标'),
            'visible' => $this->integer()->comment('是否显示'),
            'order' => $this->integer()->comment('排序'),
            'data' => $this->binary()->comment('自定义options'),
            "FOREIGN KEY ([[parent]]) REFERENCES {$menuTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
        ], $tableOptions);


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(Configs::instance()->menuTable);
    }
}
