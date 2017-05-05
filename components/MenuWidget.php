<?php
namespace mdm\admin\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
/**
 * Class Menu
 * Theme menu widget.
 */
class MenuWidget extends \yii\widgets\Menu
{
    /**
     * @inheritdoc
     */
    public $linkTemplate = '<a href="{url}">{icon} {label}</a>';
    public $submenuTemplate = "\n<ul class='treeview-menu' {show}>\n{items}\n</ul>\n";
    public $activateParents = true;
    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {
        if(isset($item['items'])) {
            $labelTemplate = '<a href="{url}">{label} <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
            $linkTemplate = '<a href="{url}">{icon} {label} <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
        }
        else {
            $labelTemplate = $this->labelTemplate;
            $linkTemplate = $this->linkTemplate;
        }
        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $linkTemplate);
            $replace = !empty($item['icon']) ? [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>'.$item['label'].'</span>',
                '{icon}' => '<i class="' . $item['icon'] . '"></i> '
            ] : [
                '{url}' => Url::to($item['url']),
                '{label}' => '<span>'.$item['label'].'</span>',
                '{icon}' => null,
            ];
            return strtr($template, $replace);
        } else {
            $template = ArrayHelper::getValue($item, 'template', $labelTemplate);
            $replace = !empty($item['icon']) ? [
                '{label}' => '<span>'.$item['label'].'</span>',
                '{icon}' => '<i class="' . $item['icon'] . '"></i> '
            ] : [
                '{label}' => '<span>'.$item['label'].'</span>',
            ];
            return strtr($template, $replace);
        }
    }
    /**
     * Recursively renders the menu items (without the container tag).
     * @param array $items the menu items to be rendered recursively
     * @return string the rendering result
     */
    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag = ArrayHelper::remove($options, 'tag', 'li');
            $class = [];
            if ($item['active']) {
                $class[] = $this->activeCssClass;
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            if (!empty($class)) {
                if (empty($options['class'])) {
                    $options['class'] = implode(' ', $class);
                } else {
                    $options['class'] .= ' ' . implode(' ', $class);
                }
            }
            $menu = $this->renderItem($item);
            if (!empty($item['items'])) {
                $menu .= strtr($this->submenuTemplate, [
                    '{show}' => $item['active'] ? "style='display: block'" : '',
                    '{items}' => $this->renderItems($item['items']),
                ]);
            }
            $lines[] = Html::tag($tag, $menu, $options);
        }
        return implode("\n", $lines);
    }
    /**
     * @inheritdoc
     */
    protected function normalizeItems($items, &$active)
    {
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (!isset($item['label'])) {
                $item['label'] = '';
            }
            $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
            $items[$i]['label'] = $encodeLabel ? Html::encode($item['label']) : $item['label'];
            $items[$i]['icon'] = isset($item['icon']) ? $item['icon'] : '';
            $hasActiveChild = false;
            if (isset($item['items'])) {
                $items[$i]['items'] = $this->normalizeItems($item['items'], $hasActiveChild);
                if (empty($items[$i]['items']) && $this->hideEmptyItems) {
                    unset($items[$i]['items']);
                    if (!isset($item['url'])) {
                        unset($items[$i]);
                        continue;
                    }
                }
            }
            if (!isset($item['active'])) {
                if ($this->activateParents && $hasActiveChild || $this->activateItems && $this->isItemActive($item)) {
                    $active = $items[$i]['active'] = true;
                } else {
                    $items[$i]['active'] = false;
                }
            } elseif ($item['active']) {
                $active = true;
            }
        }
        return array_values($items);
    }
    /**
     * Checks whether a menu item is active.
     * This is done by checking if [[route]] and [[params]] match that specified in the `url` option of the menu item.
     * When the `url` option of a menu item is specified in terms of an array, its first element is treated
     * as the route for the item and the rest of the elements are the associated parameters.
     * Only when its route and parameters match [[route]] and [[params]], respectively, will a menu item
     * be considered active.
     * @param array $item the menu item to be checked
     * @return boolean whether the menu item is active
     */
    protected function isItemActive($item)
    {
        if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $route = $item['url'][0];
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }
            $arrayRoute = explode('/', ltrim($route, '/'));
            $arrayThisRoute = explode('/', $this->route);


            //multi-controller 多controller激活 高亮状态
            if (isset($item['options']) && isset($item['options']['multi-controller']) && $item['options']['multi-controller']) {
                if (is_array($item['options']['multi-controller']) && in_array($arrayThisRoute[0], $item['options']['multi-controller'])) {
                    return true;
                }
                if (is_string($item['options']['multi-controller']) && $arrayThisRoute[0] === $item['options']['multi-controller']) {
                    return true;
                }
            }
            if (isset($item['multi_controller']) && $item['multi_controller']) {
                if (strpos($item['multi_controller'],",")){
                    $multi_controller_array = explode(",",$item['multi_controller']);
                    if(in_array($arrayThisRoute[0], $multi_controller_array)){
                        return true;
                    }
                }
                if (!strpos($item['multi-controller'],",") && $arrayThisRoute[0] === $item['multi-controller']) {
                    return true;
                }
            }


            //改写了路由的规则，是否高亮判断到controller而非action
            $routeCount = count($arrayRoute);
            if ($routeCount == 2) {
                if ($arrayRoute[0] !== $arrayThisRoute[0]) {
                    return false;
                }
            } elseif ($routeCount == 3) {
                if ($arrayRoute[0] !== $arrayThisRoute[0]) {
                    return false;
                }
                if (isset($arrayRoute[1]) && $arrayRoute[1] !== $arrayThisRoute[1]) {
                    return false;
                }
            } else {
                return false;
            }

//            if ($arrayRoute[0] !== $arrayThisRoute[0]) {
//                return false;
//            }
//            if (isset($arrayRoute[1]) && $arrayRoute[1] !== $arrayThisRoute[1]) {
//                return false;
//            }
//            if (isset($arrayRoute[2]) && $arrayRoute[2] !== $arrayThisRoute[2]) {
//                return false;
//            }


            unset($item['url']['#']);
            if (count($item['url']) > 1) {
                foreach (array_splice($item['url'], 1) as $name => $value) {
                    if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * menuHelperCallback
     * @param $menu
     * @return array
     */
    public static function menuHelperCallback($menu){
        $data = json_decode($menu['data'], true);
        $items = $menu['children'];
        $return = [
            'label' => $menu['name'],
            'url' => [$menu['route']],

            'visible' => $menu['visible'],
        'icon' => $menu['icon'],
        'multi-controller' => isset($menu['multi-controller'])?$menu['multi-controller']:null,
        ];
        //处理我们的配置
        if ($data) {
            //visible
            //isset($data['visible']) && $return['visible'] = $data['visible'];
            //icon
            //isset($data['icon']) && $data['icon'] && $return['icon'] = $data['icon'];



//            $data['visible'] = $menu['visible'];
//            $data['icon'] = $menu['icon'];
//            $data['multi-controller'] = $menu['multi-controller'];
            //other attribute e.g. class...
            $return['options'] = $data;
        }
        //没配置图标的显示默认图标
        (!isset($return['icon']) || !$return['icon']) && $return['icon'] = 'fa fa-circle-o';
        $items && $return['items'] = $items;
        return $return;
    }
}