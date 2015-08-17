<?php
/**
 * Created by PhpStorm.
 * User: olebar
 * Date: 2014/10/22
 * Time: 16:30:15
 */

namespace mdm\admin\controllers;

use yii\caching\ChainedDependency;
use yii\caching\ExpressionDependency;
use yii\caching\DbDependency;
use kartik\widgets\ActiveForm;
use Yii;
use yii\web\Controller;
use mdm\admin\models\Menu2 as Menu;
use yii\web\Response;

class SysController extends Controller
{
    /**
     * 强制刷新菜单
     * @return \yii\web\Response
     */
    public function actionReflushmenu()
    {
        Yii::$app->session->setFlash('reflush');
        return $this->goHome();
    }
    /**
     * 菜单管理
     * @return string
     */
    public function actionMenu()
    {

        //缓存一个带有依赖的缓存
        $key = '_menu' . Yii::$app->user->id;
        //var_dump([Yii::$app->session->getFlash('reflush') , Yii::$app->cache->get($key)]);
        //die('aaa');
        //if (Yii::$app->session->getFlash('reflush') || !Yii::$app->cache->get($key)) {
            //如果缓存依赖发生改变，重新生成缓存
            $dp = new ExpressionDependency([
                'expression' => 'count(Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->id))'
            ]);
            $authManager = new \yii\rbac\DbManager();
            $dp2 = new DbDependency([
                'sql' => "select max(updated_at) from ".$authManager->itemTable,//"{{%auth_item}}",
            ]);
            Yii::$app->cache->set($key, 'nothing', 0, new ChainedDependency([
                'dependencies' => [$dp, $dp2]
            ]));
            //利用上面的缓存依赖生成菜单的永久缓存
            $_list = Menu::generateMenuByUser();
            //var_dump($_list);exit;
            Yii::$app->cache->set('menulist-' . Yii::$app->user->id, $_list, 0);
        //}

        //var_dump(Yii::$app->cache->get('menulist-'.Yii::$app->user->id));





        $list = Menu::find()->where('level=1')->all();
        return $this->render('index', [
            'list' => $list,
        ]);
    }

    /**
     * 添加新菜单
     * @return string|Response
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new Menu;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success');
            return $this->redirect(['sys/menu']);
        } else {
            $model->loadDefaultValues();
            $model->parentid = $request->get('pid', 0);
            $model->level = $request->get('level', 0) + 1;
            return $this->render('create', [
                'model'  => $model,
                'plevel' => $request->get('level', 0)
            ]);
        }
    }

    /**
     * 更新菜单
     * @param $id
     * @return string|Response
     */
    public function actionUpdate($id)
    {
        $model = Menu::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success');
            return $this->redirect(['sys/menu']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除菜单
     * @return Response
     * @throws \Exception
     */
    public function actionMenudel()
    {
        $id = Yii::$app->request->get('id');
        $level = Yii::$app->request->get('level');
        //循环删除是为了在afterDelete删除对应的permission
        //一级菜单先删除孙子节点
        if ($level == 1) {
            $son = Menu::find()->where(['parentid' => $id, 'level' => 2])->all();
            foreach ($son as $s) {
                $gsons = Menu::find()->where(['parentid' => $s->id])->all();
                foreach ($gsons as $g) {
                    $g->delete();
                }
            }
        }
        //一二级菜单删除儿子节点
        if ($level <= 2) {
            $son = Menu::find()->where(['parentid' => $id])->all();
            foreach ($son as $s) {
                $s->delete();
            }
        }
        //删除自身
        Menu::findOne($id)->delete();
        Yii::$app->session->setFlash('success');
        return $this->redirect(['sys/menu']);
    }

    /**
     * Ajax 验证菜单名称
     * @return array
     */
    public function actionAjaxvalidate()
    {
        if ($id = Yii::$app->request->post('id')) {
            $model = Menu::findOne($id);
        } else {
            $model = new Menu();
        }
        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model, 'menuname');
        }
    }
}