<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 16:59
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use think\Controller;
use think\Request;
use app\admin\logic\SystemNode;
use service\DataService;
use service\ToolsService;
use think\Db;
use think\View;

class BaseController extends BasicAdmin{
    function _initialize(){
        parent::_initialize();
        SystemNode::applyAuthNode();
        $list = Db::name('SystemMenu')->where('status', '1')->order('sort asc,id asc')->select();
        //dump(ToolsService::arr2tree($list));
        $menus = $this->_filterMenu(ToolsService::arr2tree($list));
        //dump(ToolsService::arr2tree($list));
        $this->assign('menus', $menus);
    }



    /**
     * 后台主菜单权限过滤
     * @param array $menus
     * @return array
     */
    private function _filterMenu($menus) {
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_filterMenu($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif (stripos($menu['url'], 'http') === 0) {
                continue;
            } elseif ($menu['url'] !== '#' &&  SystemNode::checkAuthNode(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
                $menu['url'] = url($menu['url']);
            } else {
                unset($menus[$key]);
            }
        }
        return $menus;
    }
}