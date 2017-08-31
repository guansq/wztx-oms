<?php

namespace app\admin\controller;

use think\Request;
use service\LogService;

class Order extends BaseController {
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    private $stauusLists = [
        ''=>'',
        'init' => '初始状态',
        'hang' => '挂起',
        'quote' => '报价中',
        'quoted' => '已报价-未配送',
        'distribute' => '配送中',
        'photo' => '拍照完毕',
        'pay_failed' => '支付失败',
        'pay_success' => '支付成功',
        'comment' => '已评论'
    ];

    public function index() {

        return view();
    }
    public function test() {

        return view();
    }

    /**
     * 得到订单列表
     */

    public function getOrderList() {
        $where = [];
        $get = input('param.');
        $orderby='';
        if(isset($get['order'][0])){
            $orderby = $get['columns'][$get['order'][0]['column']]['data'].' '.$get['order'][0]['dir'];
            //var_dump($orderby);
        }
        //性别 车牌/姓名 省
        // 应用搜索条件
        foreach (['name', 'status', 'tran_type', 'is_insured', 'create_at','is_clear','pay_cer_pic'] as $key) {
            //$get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['a.order_code|c.card_number|org_address_name|dest_address_name|dest_address_detail|org_address_detail|dr_name|a.real_name|a.company_name|a.dr_phone|a.car_style_length|a.car_style_type'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'is_insured') {
                    if ($get[$key] == 1) {
                        $where['a.premium_amount'] = array('gt', 0);
                    } else {
                        $where['a.premium_amount'] = array('elt', 0);
                    }
                } elseif ($key == 'create_at') {
                    $where['a.create_at'] = array('between', array(strtotime($get[$key]), strtotime($get[$key]) + 86400));
                }elseif ($key == 'pay_cer_pic') {
                    if( $get[$key] == 0){
                        $where['a.pay_cer_pic'] = ['exp','is null'];
                    }else{
                        $where['a.pay_cer_pic'] = ['exp','is not null'];
                    }
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $orderLogic = Model('Order', 'logic');
        $list = $orderLogic->getListInfo($start, $length, $where,$orderby);
        //  var_dump($list);
        $returnArr = [];
        $num = 0;
        foreach ($list as $k => $v) {
            //$logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
            $returnArr[] = [
                'id' => $v['id'],//id
                'num' => ++$num,
                'order_code' => $v['order_code'],//订单号
                'policy_code' => $v['policy_code'],//保单号
                'card_number' => $v['card_number'],//车牌
                'is_ensured' => ($v['premium_amount'] > 0) ? '保险' : '未保险',//货物保险状态
                'org_address_name' => $v['org_address_name'],//出发地
                'org_address_detail' => $v['org_address_detail'],//出发地
                'dest_address_name' => $v['dest_address_name'],//目的地
                'dest_address_detail' => $v['dest_address_detail'],//目的地
                'status' => $this->stauusLists[$v['status']],//状态
                'clearstauts'=>(empty($v['is_clear'])?'未结算':'结算'),
                'create_at'=>empty($v['create_at'])?'':date('Y-m-d',$v['create_at']),//创建时间
                'dr_name'=>$v['dr_name'],//司机姓名
                'sp_name'=>($v['customer_type'] == 'person')?$v['real_name']:$v['company_name'],//货主姓名
                'dr_phone'=>$v['dr_phone'],//司机电话
                'car_style_length'=>$v['car_style_length'],//车长
                'car_style_type'=>$v['car_style_type'],//车型
                'pay_cer_pic'=>empty($v['pay_cer_pic'])?'否':'是',//是否有上传支付凭证
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Order/showdetail', ['id' => $v['id']]) . '" >查看</a>
                                <a class="hang-up" href="javascript: void(0);" data-field="status" data-value="hang" data-update="' . $v['id'] . '" data-action="' . url('Order/hang', ['id' => $v['id']]) . '">挂起</a>
',];
            //            <a class="settle" href="javascript: void(0);"  data-field="is_clear" data-value="1" data-update="' . $v['id'] . '" data-action="' . url('Order/clear', ['id' => $v['id']]) . '">结算</a>
        }
        $total = $orderLogic->getListNum($where);
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
    public function showdetail($id) {
        $id = intval(input('id'));
        $where = ['id' => $id];
        $orderLogic = Model('Order', 'logic');
        $list = $orderLogic->getListOneInfo($where);
        if (empty($list)) {
            $this->error('未查询到当前订单信息', '');
        }
        $list['statusname'] = $this->stauusLists[$list['status']];
        $list['clearstauts'] = (empty($list['is_clear'])?'未结算':'结算');
        $pay_cer_pic = explode('|', $list['pay_cer_pic']);
        $pay_cer_pic = array_filter($pay_cer_pic);

        $this->assign('list', $list);
        //凭证信息
        $this->assign('pay_cer_pic', $pay_cer_pic);
        //var_dump($pay_cer_pic);
//        //收货信息
//        $addressdetail = $orderLogic->getAddressInfo(['id' => $list[0]['dest_address_id']]);
//        $this->assign('dest_address', $addressdetail[0]);
//        //寄件信息
//        $addressdetail = $orderLogic->getAddressInfo(['id' => $list[0]['org_address_id']]);
//        $this->assign('org_address', $addressdetail[0]);
        //车辆信息
        $carinfo = $orderLogic->getCardInfo(['a.id' => $list['dr_id']]);

        $this->assign('carinfo',empty($carinfo[0])?'':$carinfo[0] );
        return view('edit');
    }

    //审核通过
    public function pass() {
//        $titile = input('title');
//        if (empty($titile)) {
//            return json(['code' => 4000, 'msg' => '输入文本不能为空', 'data' => []]);
//        }

        $id = input('id');
        $orderLogic = model('Order', 'logic');
        $status = ['per_status' => 'pass', 'update_at' => time(), 'pay_time' => time(), 'status' => 'pay_success','is_pay'=>1,'payway'=>4];
        $detail = $orderLogic->updateStatus(['id' => $id], $status);
        if ($detail) {
            LogService::write('订单:' . $id, '凭证审核通过');
            $list = $orderLogic->getListOneInfo( ['id' => $id]);
            if (empty($list)) {
                return json(['code' => 4000, 'msg' => '未查询到当前订单信息', 'data' => []]);
            }

            $sp_id =  $list['sp_id'];
            $push_token = getSpPushToken($sp_id);
            saveOrderShare($id);//存入推荐列表
            clearOrder($id); //结算订单
            if(!empty($push_token)){
                $titlepush = '凭证审核通过';
                $contentpush = '订单:'.$list['order_code'].'，凭证审核通过';
                sendMsg($sp_id,$titlepush,$contentpush,0);
                pushInfo($push_token,$titlepush,$contentpush,'wztx_shipper');//推送给货主
            }
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write('订单:' . $id, '凭证审核更新失败');
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }

    //订单挂起
    public function hang() {
        $id = input('id');
        $orderLogic = model('Order', 'logic');
        $status = ['status' => 'hang', 'update_at' => time()];
        $detail = $orderLogic->updateStatus(['id' => $id,'status'=>['exp','in ("init","quote","quoted")']], $status);
        if ($detail) {
            LogService::write('订单:' . $id, '订单挂起成功');
            $this->success('更新成功！', '');
        } else {
            LogService::write('订单:' . $id, '订单挂起失败');
            $this->error('更新失败！', '');
        }
    }
    //订单结算
    public function clear() {
        $this->error('订单结算失败！', '');
        $id = input('id');
        $orderLogic = model('Order', 'logic');
        //状态为支付成功，或者评论的时候更新结算状态,计算结算金额,更新base表里面总的金额   floor($num*100)/100
        $list = $orderLogic->getListOneInfo( ['id' => $id]);
        if (empty($list)) {
            $this->error('未查询到当前订单信息', '');
        }
        $final_price = $list['final_price'];
        $clear_price =floor($final_price*(100-sysconf('clear_percent')))/100;
        $status = ['is_clear' => '1', 'update_at' => time(),'clear_price'=>$clear_price];
        $where = ['id' => $id,'status'=>['exp','in ("pay_success","comment")'],'is_clear' => '0'];
        $detail = $orderLogic->updateStatus($where, $status);
        if ($detail) {
            $dr_id =  $list['dr_id'];
            $driverLogic = model('Driver', 'logic');
            $status = ['cash'=>['exp','cash+'.$clear_price], 'update_at' => time()];
            $detail = $driverLogic->updateStatus(['id' => $dr_id], $status);
            if($detail){
                LogService::write('订单:' . $id, '订单结算成功'.$clear_price);
                $push_token = getDrPushToken($dr_id);
                if(!empty($push_token) && !empty($clear_price)){
                    $titlepush = '有一笔订单结算成功';
                    $contentpush = '订单:'.$list['order_code'].'，结算金额:'.$clear_price.'元';
                    sendMsg($dr_id,$titlepush,$contentpush,1);
                    pushInfo($push_token,$titlepush,$contentpush,'wztx_driver');//推送给司机
                }
                $this->success('更新成功！', '');
            }else{
                LogService::write('订单:' . $id, '订单状态更改成功，基本信息表剩余金额更新失败');
                $this->error('更新失败！', '');
            }
        } else {
            LogService::write('订单:' . $id, '订单结算失败');
            $this->error('订单结算失败！', '');
        }
    }

    public function auth() {
        $titile = input('title');
        $id = input('id');
        $authtype = input('type');
        $orderLogic = model('Order', 'logic');
        $status = ['per_status' => 'refuse', 'update_at' => time()];
        $where = ['id' => $id];
        if (empty($titile)) {
            return json(['code' => 4000, 'msg' => '输入文本不能为空', 'data' => []]);
        }
        switch ($authtype) {
            case 'refuse': //拒绝审核
                $status['per_status'] = 'refuse';
                $tmp = $titile;// . ',' . time();
                // $where['per_status'] = 'init';
                $status['per_remark'] = $tmp;
                break;
            default:
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }

        $detail = $orderLogic->updateStatus($where, $status);
        if ($detail) {
            $orderLogic = model('Order', 'logic');
                $list = $orderLogic->getListOneInfo( ['id' => $id]);
            if (empty($list)) {
                return json(['code' => 4000, 'msg' => '未查询到当前订单信息', 'data' => []]);
            }
            $sp_id =  $list['sp_id'];
            $push_token = getSpPushToken($sp_id);
            if(!empty($push_token)){
                $titlepush = '支付凭证被拒绝';
                $contentpush = '订单:'.$list['order_code'].'，支付凭证被拒绝原因:'.$tmp;
                sendMsg($sp_id,$titlepush,$contentpush,0);
                pushInfo($push_token,$titlepush,$contentpush,'wztx_shipper');//推送给货主
            }
            LogService::write('订单页面:' . $id, $authtype . ',' . $titile . ',' . time().'更新成功');
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write('订单页面:' . $id, $authtype . ',' . $titile . ',' . time().'更新失败');
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }


    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create() {
        //
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
        return view('edit');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id) {
        //
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
