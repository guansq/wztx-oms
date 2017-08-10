<?php

namespace app\admin\controller;

use think\Request;
use service\LogService;

class Withdraw extends BaseController {


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
        return view();
    }

    /**
     * 得到提现列表-司机和货主端
     */

    public function getWithDrawList() {
        $where = [];
        $get = input('param.');
        // var_dump($get);
        foreach (['orderno', 'type', 'phone', 'name', 'applytime', 'status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['real_name'] = ['like', "%{$get[$key]}%"];
                } else if ($key == 'orderno') {
                    $where['withdraw_code'] = $get[$key];
                } else if ($key == 'applytime') {
                    $where['create_at'] = array('between', array(strtotime($get[$key]), strtotime($get[$key]) + 86400));
                    // $where['create_at'] =strtotime($get[$key]);
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $withdrawLogic = Model('Withdraw', 'logic');
        $listAll = $withdrawLogic->getListInfo($start, $length, $where);

        $returnArr = [];
        $num = 0;
        foreach ($listAll as $k => $v) {
            $types = ['person' => '个人货主端', 'company' => '公司货主', 'driver' => '司机端'];
            $results = ['init' => '未处理', 'agree' => '后台同意', 'refuse' => '已拒绝', 'pay_success' => '银行返回成功', 'pay_fail' => '银行返回失败'];
            $num = $num + 1;
            //  $action = '';
            $returnArr[] = [
                'id' => $v['id'],//id
                'num' => $num,
                'orderno' => $v['withdraw_code'],//订单号
                'name' => $v['real_name'],//真实姓名
                'type' => $types[$v['type']],//真实姓名
                'phone' => $v['phone'],//手机号
                'applytime' => date('Y-m-d', $v['create_at']),//申请时间
                'amount' => $v['amount'],//申请金额
                'balance' => $v['balance'],// 账户余额
                'status' => $results[$v['status']],//状态
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Withdraw/showdetail', ['id' => $v['id']]) . '" >查看</a>',
            ];
        }
        $total = $withdrawLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    /**
     *显示详情
     *
     * @param  int $id
     * @return \think\Response
     */
    public function showdetail() {
        $id = intval(input('id'));
        $withdrawLogic = Model('Withdraw', 'logic');
        $item = $withdrawLogic->getListItem(['id' => $id]);
        if (empty($item)) {
            $this->error('未查询到当前用户信息', '');
        }
        $returnArr = $item;
        $results = ['init' => '未处理', 'agree' => '后台同意', 'refuse' => '已拒绝', 'pay_success' => '银行返回成功', 'pay_fail' => '银行返回失败'];

        $returnArr['statusinfo'] = $results[$returnArr['status']];
        $this->assign('id', $id);
        $this->assign('item', $returnArr);
        return view('edit');
    }

    //处理结果
    public function dealresult() {
        if (!in_array(input('status'), ['agree', 'refuse'])) {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => ['msg' => '状态不合法']]);
        }
        if (empty(input('remark'))) {
            return json(['code' => 4000, 'msg' => '备注不能为空', 'data' => ['msg' => '备注不能为空']]);
        }
        //后期添加提现具体操作
        $id = input('id');
        $withdrawLogic = Model('Withdraw', 'logic');
        $result = ['status' => input('status'), 'remark' => input('remark'), 'update_at' => time()];
        $detail = $withdrawLogic->updateStatus(['id' => $id], $result);
        if ($detail) {
            LogService::write(input('status') . '--' . $id, '提现');
            $item = $withdrawLogic->getListItem(['id' => $id]);
            if(input('status') == 'agree'){
                if (!empty($item)) {
                    $push_token = getDrPushToken($item['base_id']);
                    if (!empty($push_token)) {
                        $titlepush = '通过提现审核';
                        $contentpush = '通过提现审核';
                        sendMsg($item['base_id'], $titlepush, $contentpush, 1);
                        pushInfo($push_token, $titlepush, $contentpush, 'wztx_driver');//推送给司机
                    }
                }
            }
            if(input('status') == 'refuse'){
                if (!empty($item)) {
                    $real_amount = $item['real_amount'];
                    $driverLogic = model('Driver', 'logic');
                    $status = ['cash' => 'cash+'.$real_amount, 'update_at' => time()];
                    $detail = $driverLogic->updateStatus(['id' => $item['base_id']], $status);
                    $push_token = getDrPushToken($item['base_id']);
                    if (!empty($push_token)) {
                        $titlepush = '拒绝提现审核';
                        $contentpush = '拒绝提现审核原因:'.input('remark');
                        sendMsg($item['base_id'], $titlepush, $contentpush, 1);
                        pushInfo($push_token, $titlepush, $contentpush, 'wztx_driver');//推送给司机
                    }
                }
            }
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write(input('status') . '--' . $id, '提现失败');
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create() {
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request) {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id) {
        //
        return view('edit');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id) {

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id) {
        //
    }
}
