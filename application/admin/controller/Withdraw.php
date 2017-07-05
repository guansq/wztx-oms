<?php

namespace app\admin\controller;

use think\Request;

class Withdraw extends BaseController{


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        return view();
    }

    /**
     * 得到提现列表-司机和货主端
     */

    public function getWithDrawList() {
        $where = [];
        $get = input('param.');
       // var_dump($get);
        foreach (['orderno','type','phone','name','applytime','status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['name'] = ['like', "%{$get[$key]}%"];
                }else if ($key == 'orderno') {
                    $where['withdraw_orderid'] =$get[$key];
                }else if ($key == 'applytime') {
                    $where['create_at'] =strtotime($get[$key]);
                }else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $withdrawLogic = Model('Withdraw', 'logic');
        $listAll = $withdrawLogic->getListInfo($start, $length, $where);

        $returnArr = [];
        foreach ($listAll as $k => $v) {
            $types = [0 => '个人货主端', 1 => '公司货主', 2 => '司机端'];
            $results = [0=>'未处理',1=>'0处理成功',2=>'已拒绝'];

            //  $action = '';
            $returnArr[] = [
                'id' => $v['id'],//id
                'orderno' => $v['withdraw_orderid'],//订单号
                'name' => $v['name'],//真实姓名
                'type' =>$types[ $v['type']],//真实姓名
                'phone' => $v['phone'],//手机号
                'applytime' => $v['create_at'],//申请时间
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
    public function showdetail($id)
    {
        $where = [];
        $type = 0;
        $id = intval(input('id'));
        $withdrawLogic = Model('Withdraw', 'logic');
        $item = $withdrawLogic->getListItem($id);
        if (empty($item)) {
            $this->error('未查询到当前用户信息', '');
        }
        $returnArr = $item[0];
        $this->assign('id', $id);
        $this->assign('item', $returnArr);
        return view('edit');
    }

    //处理结果
    public function dealresult()
    {
        $id = input('id');
        $withdrawLogic = Model('Withdraw', 'logic');
        $result = ['status' =>input('status'),'remark'=>input('remark'), 'update_at' => time()];
        $detail = $withdrawLogic->updateStatus(['id' => $id], $result);
        // session('user', $user);
       // LogService::write('货主端:' . $id, '审核通过');
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(){
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request){
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id){
        //
        return view('edit');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id){

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int            $id
     * @return \think\Response
     */
    public function update(Request $request, $id){
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id){
        //
    }
}
