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

use app\admin\logic\SystemNode;
use service\DataService;
use service\ToolsService;
use think\Db;
use think\View;
use service\HttpService;
use think\Validate;

/**
 * 后台入口
 * Class Index
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 10:41
 */
class Index extends BaseController {


    /**
     * 后台框架布局
     * @return View
     */
    public function index() {
        //NodeModel::applyAuthNode();
        //$list = Db::name('SystemMenu')->where('status', '1')->order('sort asc,id asc')->select();
        //$menus = $this->_filterMenu(ToolsService::arr2tree($list));
        $this->assign('title', '系统管理');
        //$this->assign('menus', $menus);
        return view();
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus
     * @return array

    private function _filterMenu($menus) {
     * foreach ($menus as $key => &$menu) {
     * if (!empty($menu['sub'])) {
     * $menu['sub'] = $this->_filterMenu($menu['sub']);
     * }
     * if (!empty($menu['sub'])) {
     * $menu['url'] = '#';
     * } elseif (stripos($menu['url'], 'http') === 0) {
     * continue;
     * } elseif ($menu['url'] !== '#' && auth(join('/', array_slice(explode('/', $menu['url']), 0, 3)))) {
     * $menu['url'] = url($menu['url']);
     * } else {
     * unset($menus[$key]);
     * }
     * }
     * return $menus;
     * }*/

    /**
     * 主机信息显示
     * @return View
     */
    public function main() {
        $orderLogic = model('Order', 'logic');
//        $hangwhere = ['status' => 'hang']; 挂起订单
//        $hangnum = $orderLogic->getListTotalNum($hangwhere);
        $clearwhere = ['is_clear' => '1'];
        $clearnum = $orderLogic->getListTotalNum($clearwhere);
        $unclearwhere = ['is_clear' => '0', 'status' =>['exp', 'in ("photo")'],  'pay_cer_pic' => ['exp', 'is not null']];
        $unclearnum = $orderLogic->getListTotalNum($unclearwhere);
        $spchecknum = model('Shipper', 'logic')->getListTotalNum(['auth_status' => 'check']);
        $drchecknum = model('Driver', 'logic')->getListTotalNum(['auth_status' => 'check']);
        $begin_time_7days = strtotime(date('Y-m-d 00:00:00', strtotime('this week')));
        $end_time_7days = strtotime(date('Y-m-d')) + 86400 - 1;
        $begin_time_today = strtotime(date('Y-m-d'));
        $end_time_today = strtotime(date('Y-m-d')) + 86400 - 1;
        $where_7days['pay_time'] = array('between', array($begin_time_7days, $end_time_7days));
        $result_7days = $orderLogic->getSuccessTotal($where_7days);
        $where_today['pay_time'] = array('between', array($begin_time_today, $end_time_today));
        $result_today = $orderLogic->getSuccessTotal($where_today);
        $where_today_base['create_at'] = array('between', array($begin_time_today, $end_time_today));
        $where_7days_base['create_at'] = array('between', array($begin_time_7days, $end_time_7days));
        $where_today_base_with =[
            'result_time'=>array('between', array($begin_time_today, $end_time_today)),
            'status' =>'agree'
        ] ;
        $where_7days_base_with =[
            'result_time'=>array('between', array($begin_time_7days, $end_time_7days)),
            'status' =>'agree'
        ] ;
        $where_today_base_clear =[
            'create_at'=>array('between', array($begin_time_today, $end_time_today)),
            'is_clear' =>'1'
        ] ;
        $where_7days_base_clear =[
            'create_at'=>array('between', array($begin_time_7days, $end_time_7days)),
            'is_clear' =>'1'
        ] ;
        $spnewnum = model('Shipper', 'logic')->getListTotalNum($where_today_base);
        $spnewnum7d = model('Shipper', 'logic')->getListTotalNum($where_7days_base);
        $drnewnum = model('Driver', 'logic')->getListTotalNum($where_today_base);
        $drnewnum7d = model('Driver', 'logic')->getListTotalNum($where_7days_base);
        $withdrawtotal = model('Withdraw', 'logic')->getListTotal($where_today_base_with); //今日提现
        $withdrawtotal7d = model('Withdraw', 'logic')->getListTotal($where_7days_base_with);//7天提现
        $cleartotal = model('Order', 'logic')->getListTotalNum($where_today_base_clear); //今日提现
        $cleartotal7d = model('Order', 'logic')->getListTotalNum($where_7days_base_clear);//7天提现
        $unwithdraw = model('Withdraw', 'logic')->getListTotalNum(['status'=>'init']);//提现待审核数量

        $list = [
        //    'hangnum' => $hangnum,
            'unclearnum' => $unclearnum,
        //    'clearnum' => $clearnum,
            'unwithdraw' => $unwithdraw, //未审核提现
            'spchecknum' => $spchecknum, //待审核货主
            'drchecknum' => $drchecknum, //待审核司机
            'spnew' => $spnewnum , //今日新增货主
            'drnew' => $drnewnum, //今日新增司机
            'spnew7d' =>  $spnewnum7d,//本周新增货主
            'drnew7d' => $drnewnum7d,//本周新增司机
            'withdrawtotal' =>  number_format($withdrawtotal[0]['withdraw_total'], 2, '.', ','),
            'withdrawtotal7d' =>  number_format($withdrawtotal7d[0]['withdraw_total'], 2, '.', ','),
            'cleartotal' =>  number_format($cleartotal[0]['tran_total'], 2, '.', ','),
            'cleartotal7d' =>  number_format($cleartotal7d[0]['tran_total'], 2, '.', ','),
            'order_amount_7d' => $result_7days[0]['order_amount'],
            'tran_total_7d' => number_format($result_7days[0]['tran_total'], 2, '.', ','),
            'order_amount_today' => $result_today[0]['order_amount'],
            'tran_total_today' => number_format($result_today[0]['tran_total'], 2, '.', ','),
        ];
//        var_dump($list);
        $this->assign('list', $list);
        //  echo  number_format('1000000000000.015',2,'.',',');
        //var_dump(sysconf('clear_percent'));
        $_version = Db::query('select version() as ver');
        $version = array_pop($_version);
        $this->assign('mysql_ver', $version['ver']);
        if (session('user.username') === 'admin' && session('user.password') === '21232f297a57a5a743894a0e4a801fc3') {
            $url = url('admin/index/pass') . '?id=' . session('user.id');
            $alert = [
                'type' => 'danger',
                'title' => '安全提示',
                'content' => "超级管理员默认密码未修改，建议马上<a href='javascript:void(0)' data-modal='{$url}'>修改</a>！"
            ];
            $this->assign('alert', $alert);
            $this->assign('title', '后台首页');
        }
        return view();
    }

    /**
     * 修改密码
     */
    public function pass() {
        /*if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }*/
        if (intval($this->request->request('id')) !== intval(session('user.id'))) {
            $this->error('访问异常！');
        }
        if ($this->request->isGet()) {
            $this->assign('verify', true);
            return $this->_form('SystemAdmin', 'user/pass');
        } else {
            $data = $this->request->post();
            if ($data['password'] !== $data['repassword']) {
                $this->error('两次输入的密码不一致，请重新输入！');
            }
            if ($data['password'] === $data['oldpassword']) {
                $this->error('新密码和旧密码一致，请重新输入！');
            }
            $user = Db::name('SystemAdmin')->where('id', session('user.id'))->find();
            if (md5($data['oldpassword']) !== $user['password']) {
                $this->error('旧密码验证失败，请重新输入！');
            }
            if (DataService::save('SystemAdmin', ['id' => session('user.id'), 'password' => md5($data['password'])])) {
                $this->success('密码修改成功，下次请使用新密码登录！', '');
            } else {
                $this->error('密码修改失败，请稍候再试！');
            }
        }
    }

    /**
     * 修改资料
     */
    public function info() {
        /*if (in_array('10000', explode(',', $this->request->post('id')))) {
            $this->error('系统超级账号禁止操作！');
        }*/
        if (intval($this->request->request('id')) === intval(session('user.id'))) {
            return $this->_form('SystemAdmin', 'user/form');
        }
        $this->error('访问异常！');
    }

}
