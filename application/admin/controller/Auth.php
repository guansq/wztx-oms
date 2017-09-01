<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\model\AuthNode;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;
use think\Url;
/**
 * 系统权限管理控制器
 * Class Auth
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:13
 */
class Auth extends BaseController {

    /**
     * 默认数据模型
     * @var string
     */
    protected $table = 'SystemAuth';

    /**
     * 权限列表
     */
    public function index() {
       // $this->title = '系统权限管理';
        parent::_list($this->table);
    }

    /**
     * 权限授权
     * @return string|array
     */
    public function apply() {
       // var_dump($this->request->get());
        $auth_id = $this->request->get('id', '0');
      //  var_dump(strtolower($this->request->get('action', '0')));
        switch (strtolower($this->request->get('action', '0'))) {
            case 'getnode':
                $nodes = NodeService::getNodes();
                //var_dump($nodes);
                $checked = Db::name('SystemAuthNode')->where('auth', $auth_id)->column('node');
                foreach ($nodes as $key => &$node) {
                    $node['checked'] = in_array($node['node'], $checked);
                    if (empty($node['is_auth']) && substr_count($node['node'], '/') > 1) {
                        unset($nodes[$key]);
                    }
                }
                $this->success('获取节点成功！', '', $this->_filterNodes($this->_filterNodes(ToolsService::arr2tree($nodes, 'node', 'pnode', '_sub_'))));
                break;
            case 'save':
                $data = [];
                $post = $this->request->post();
                foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
                    $data[] = ['auth' => $auth_id, 'node' => $node];
                }
                Db::name('SystemAuthNode')->where('auth', $auth_id)->delete();
                Db::name('SystemAuthNode')->insertAll($data);

               // $this->success('节点授权更新成功！', '');
                $url = str_replace($_SERVER['SERVER_NAME'] . '/', $_SERVER['SERVER_NAME'] . '/#/', Url::build('Auth/index')) . '?' . $_SERVER['QUERY_STRING'];
                $url = preg_replace('/s=[^\s]*&/', '', $url);
                $this->success('节点授权更新成功！', $url);
                break;
            default :
                $this->assign('title', '节点授权');
                return $this->_form($this->table, 'apply');
        }
    }

    /**
     * 节点数据拼装
     * @param array $nodes
     * @param int $level
     * @return array
     */
    protected function _filterNodes($nodes, $level = 1) {
        foreach ($nodes as $key => &$node) {
            if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
                $node['_sub_'] = $this->_filterNodes($node['_sub_'], $level + 1);
            } elseif ($level < 3) {
                unset($nodes[$key]);
            }
        }
        return $nodes;
    }

    /**
     * 权限添加
     */
    public function add() {
        $title = input('title');
        $titledetail = Db::name($this->table)->where('title', $title)->find();
        if(!empty($titledetail)){
            $this->error("权限名称需唯一");
        }
     //   $articledetail = Db::name($this->table)->where('id', $id)->find();
//        CREATE TABLE `rt_system_auth` (
//        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//  `title` varchar(20) NOT NULL COMMENT '权限名称',
//  `status` tinyint(1) unsigned DEF
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限编辑
     */
    public function edit() {
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限禁用
     */
    public function forbid() {
        if (DataService::update($this->table)) {
            $this->success("权限禁用成功！", '');
        }
        $this->error("权限禁用失败，请稍候再试！");
    }

    /**
     * 权限恢复
     */
    public function resume() {
        if (DataService::update($this->table)) {
            $this->success("权限启用成功！", '');
        }
        $this->error("权限启用失败，请稍候再试！");
    }

    /**
     * 权限删除
     */
    public function del() {
        $ids = explode(',', input("post.id", ''));
        $successid = '';
        $failid = '';
        foreach ($ids as $k => $v){
            $adminuser = Db::name('SystemAdmin')->where('authorize', $v)->select();
            if(empty($adminuser)){
                Db::name('SystemAuthNode')->where('auth', $v)->delete();
                $successid .= $v;
            }else{
                $failid .= $v;
            }
        }
        if(empty($failid)){
            if(DataService::update($this->table)){
                $this->success("权限删除成功！", '');
            }
            $this->error("权限删除失败，请稍候再试！");
        }
        $this->error("所选权限已有对应用户在使用，修改或者删除属于当前组的用户再试！");
    }

}
