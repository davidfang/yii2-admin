<?php
/**
 * Created by PhpStorm.
 * User: olebar
 * Date: 2014/10/22
 * Time: 16:32:40
 */

namespace mdm\admin;


use yii\web\AssetBundle;

class TreeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@mdm/admin/assets';
    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $css = [
        'treeview.css',
        'font-awesome.min.css',
        //'ace.min.css',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        //'mdm\admin\AdminAsset',
        //'mdm\admin\AutocompleteAsset',
    ];
} 