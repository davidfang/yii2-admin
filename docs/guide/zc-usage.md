###使用方法
================

1. 安装
```
php composer.phar require zc/yii2-admin "dev-zc.2.0"
```
2. 配置
打开backend/config/main.php修改配置
```php
"modules" => [    
    "admin" => [        
        "class" => "mdm\admin\Module",   
    ],
],
"aliases" => [    
    "@mdm/admin" => "@vendor/zc/yii2-admin",
],
//这里必须添加authManager配置项
"components" => [   
     ...    
    //components数组中加入authManager组件,有PhpManager和DbManager两种方式,    
    //PhpManager将权限关系保存在文件里,这里使用的是DbManager方式,将权限关系保存在数据库.    
    "authManager" => [        
        "class" => 'yii\rbac\DbManager', //这里记得用单引号而不是双引号        
        "defaultRoles" => ["guest"],    
    ],   
     ...
],
//严重警告！！！as access位置不要添加错了，已经不少同学都掉坑里了！！！
'as access' => [
   //ACF肯定是要加的，因为粗心导致该配置漏掉了，很是抱歉
    'class' => 'mdm\admin\components\AccessControl',
    'allowActions' => [
        //这里是允许访问的action
        //controller/action
        '*'
    ]
],
```

可以访问地址：http://localhost/admin/menu/index

3. 调用菜单

````php

use mdm\admin\components\MenuHelper;
use mdm\admin\components\MenuWidget;

echo MenuWidget::widget([
                    'options' => ['class' => 'sidebar-menu'],
                    'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, 'mdm\admin\components\MenuWidget::menuHelperCallback'),
                ]);

````

4. 调用权限模板

```php
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module', 
    'allowedIPs' => ['127.0.0.1', '::1'], 
    'generators' => [ 
        'crud' => [ //生成器名称 
            'class' => 'yii\gii\generators\crud\Generator', 
            'templates' => [ //设置我们自己的模板 
                //模板名 => 模板路径 
                'yii2-starter-kit-copy-right' => '@vendor/zc/yii2-admin/_gii/templates', 
            ] 
        ] 
    ], 
];
```


5. 生成菜单数据


```
 console/yii  migrate/up --migrationPath='@vendor/zc/yii2-admin/migrations'

```

