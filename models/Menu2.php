<?php

namespace mdm\admin\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "t_menu".
 *
 * @property integer $id
 * @property string $menuname
 * @property integer $parentid
 * @property string $route
 * @property string $menuicon
 * @property integer $level
 */
class Menu2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menu2}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menuname','route'], 'required'],
            ['route','unique'],
            [['parentid', 'level'], 'integer'],
            [['menuname', 'route'], 'string', 'max' => 32],
            [['menuicon'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'menuname' => '菜单名称',
            'parentid' => '父类ID',
            'route' => '路由',
            'menuicon' => '图标',
            'level' => '级别',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $auth = Yii::$app->authManager;
        if($insert)
        {
            $permission = $auth->createPermission($this->route);
            $permission->description = $this->menuname;
            $auth->add($permission);
        }else
        {
            $route = ArrayHelper::getValue($changedAttributes,'route',$this->route);
            $permission = $auth->getPermission($route);
            $permission->name = $this->route;
            $permission->description = $this->menuname;
            $auth->update($route,$permission);
        }

    }

    public function afterDelete()
    {
        parent::afterDelete();
        //删除所有权限
        $auth = Yii::$app->authManager;
        if($p = $auth->getPermission($this->route))
            $auth->remove($p);
    }
    /**
     * 获取子菜单
     * @return static
     */
    public function getSon()
    {
        return $this->hasMany(self::className(),['parentid'=>'id'])->orderBy('level desc');
    }
    /**
     * 获取父菜单
     */
    public function getFather()
    {
        return $this->hasOne(self::className(),['id'=>'parentid']);
    }

    /**
     * 生成菜单
     * @return string
     */
    public static function generateMenuByUser()
    {
        $list = self::find()->where('level=1')->all();
        $menu = Yii::$app->controller->renderPartial('@mdm/admin/views/sys/_menu',[
            'list'=>$list,
            'admin'=>(Yii::$app->user->id==1)?true:false
        ]);
        return $menu;
    }
}
