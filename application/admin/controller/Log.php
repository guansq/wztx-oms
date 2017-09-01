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

use service\DataService;
use think\Db;
use service\LogService;
use think\Model;

/**
 * 系统日志管理
 * Class User
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:12
 */
class Log extends BaseController {

    /**
     * 指定当前数据表
     * @var string
     */
    protected $table = 'SystemLog';

    /**
     * 日志列表
     */
    public function index() {
        //$this->title = '系统操作日志';
        $db = Db::name('SystemLog')->order('id desc');
        $get = $this->request->get();
        foreach (['action', 'content', 'username'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        if(session('user')['username'] != 'admin'){
            $db->where('username', 'exp',"not in('admin')");
        }
        $db->where(['is_deleted'=>0]);
        $row_page = $this->request->get('rows', cookie('rows'), 'intval');
        cookie('rows', $row_page >= 10 ? $row_page : 20);
        $page = $db->paginate($row_page, false,['query' => $this->request->get()]);
        $result['list'] = $page->all();
        $result['page'] = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-load="$1" href="javascript:void(0);"', 'pagination pull-right'], $page->render());

        $this->assign('list', $result['list'] );
        $this->assign('page',   $result['page']);
      return view();
       // parent::_list($db);
       // parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $data

    protected function _index_data_filter(&$data) {
        $ip = new \Ip2Region();
        foreach ($data as &$vo) {
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '|'], ['', ' '], $vo['isp']);
        }
    }*/

    /**
     * 日志删除操作
     */
    public function del() {
        if (DataService::update($this->table)) {
            $this->success("日志删除成功！", '');
        }
        $this->error("日志删除失败，请稍候再试！");
    }

}
