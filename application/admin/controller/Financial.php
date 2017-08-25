<?php

namespace app\admin\controller;

use function MongoDB\BSON\fromJSON;
use think\Request;
use PHPExcel;
use PHPExcel_IOFactory;
use service\LogService;

class Financial extends BaseController {

    public function prDates($start, $end) { // 两个日期之间的所有日期
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        $result = [];
        while ($dt_start <= $dt_end) {
            $result[] = date('Y-m-d', $dt_start);
            $dt_start = strtotime('+1 day', $dt_start);
        }
        return $result;
    }

    /**
     * 交易统计
     *
     * @return \think\Response
     */
    public function tcStatistics() {
        $data = $this->getOptions();
        //var_dump(implode(',', $data['data_show']));
        $this->assign('is_show', $data['is_show']);
        $this->assign('order_amount', implode(',', $data['order_amount']));
        $this->assign('tran_total', implode(',', $data['tran_total']));
        $this->assign('data_show', $data['datestr']);
        return view();
    }

    /**
     * 得到交易量数据
     */

    public function getOptions() {
        $where = [];
        if (empty(input('begintime')) || empty(input('endtime'))) {
            switch (input('type')) {
                case '30days':
                    $begin_time = strtotime(date('Y-m-d')) - 86400 * 29;
                    $end_time = strtotime(date('Y-m-d'))+86400-1;
                    break;
                case '15days':
                    $begin_time = strtotime(date('Y-m-d')) - 86400 * 14;
                    $end_time = strtotime(date('Y-m-d'))+86400-1;
                    break;
                case '7days':
                    $begin_time = strtotime(date('Y-m-d')) - 86400 * 6;
                    $end_time = strtotime(date('Y-m-d'))+86400-1;
                    break;
                case 'today':
                default:
                    $begin_time = strtotime(date('Y-m-d'));
                    $end_time = strtotime(date('Y-m-d')) + 86400 - 1;
                    break;
            }
        } else {
            if ((strtotime(input('endtime')) - strtotime(input('begintime'))) > 30 * 24 * 60 * 60) {
                return (["is_show" => '2', "order_amount" => [0], "tran_total" => [0], "data_show" => [date("Y-m-d")], "datestr" => date("Y-m-d")]);
                $info = ['draw' => time(), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'extdata' => $where, 'showmsg' => '查询时间不能超过30天'];
            }
            $begin_time = strtotime(input('begintime'));
            $end_time = strtotime(input('endtime'));
        }
        $where['pay_time'] = array('between', array($begin_time, $end_time));
        // $titile = '统计';
        $datearray = $this->prDates(date('Y-m-d', $begin_time), date('Y-m-d', $end_time));
        $legend = ['order_amount' => '订单量', 'tran_total' => '交易总额'];
        //查询订单数据(pay_time在所选时间段内的)
        $orderLogic = Model('Order', 'logic');
        $result = $orderLogic->getSuccessListInfo($where);
        //$result = [0 => ['date' => '2017-07-09', 'order_amount' => 2, 'tran_total' => 1000], 1 => ['date' => '2017-07-11', 'order_amount' => 5, 'tran_total' => 10], 3 => ['date' => '2017-07-12', 'order_amount' => 8, 'tran_total' => 100]];
        if (empty($result)) {
            $datestr = '';
            foreach ($datearray as $key => $item) {
                $datestr .= ",'" . $item . "'";
            }
            $datestr = substr($datestr, 1);
            return (["is_show" => '0', "order_amount" => [0], "tran_total" => [0], "data_show" => $datearray, "datestr" => $datestr]);
        }
        $resultdeal = [];
        foreach ($result as $key => $item) {
            $resultdeal[$item['days']] = $item;
        }
        $legendlist = [];
        $keylist = [];
        while (list($key, $value) = each($legend)) {
            $legendlist[] = '"' . $value . '"';
            $keylist[] = $key;
        }
        $resultlast = [];
        $datestr = '';
        foreach ($datearray as $key => $item) {
            foreach ($keylist as $k => $vo) {
                if (!empty($resultdeal[$item])) {
                    $resultlast[$vo][] = $resultdeal[$item][$vo];
                } else {
                    $resultlast[$vo][] = 0;
                }
            }
            $datestr .= ",'" . $item . "'";
        }
        $datestr = substr($datestr, 1);
        $resultlastt = [];
        $resultlasttt = [];
        foreach ($legend as $key => $item) {
            $resultlasttt[$key]['name'] = $item;
            $resultlasttt[$key]['type'] = 'line';
            $resultlasttt[$key]['data'] = $resultlast[$key];
        }
        foreach ($resultlasttt as $key => $item) {
            $resultlastt[$key] = str_replace("\"", "'", json_encode($item));
        }
        return (["is_show" => '1', "order_amount" => $resultlasttt['order_amount']['data'], "tran_total" => $resultlasttt['tran_total']['data'], "data_show" => $datearray, "datestr" => $datestr]);
    }


    /**
     * 充值记录
     *
     * @return \think\Response
     */
    public function rechargeRecord() {
        return view();
    }

    //累计未结算
    public function unbalancedAccount() {
        return view();
    }

    public function getUnbalancedList() {
        $where = [];
        $get = input('param.');
        // 应用搜索条件
        foreach (['name', 'order_code', 'pay_time', 'policy_code'] as $key) {
            //$get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'order_code') {
                    $where['a.order_code'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'name') {
                    $where['a.company_name|a.real_name|b.real_name'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'pay_time') {
                    $where['a.pay_time'] = array('between', array(strtotime($get[$key]), strtotime($get[$key]) + 86400));
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $where['status'] = ['exp', 'in ("pay_success","comment")'];
        $where['is_clear'] = 0;
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $rechargeLogic = Model('Recharge', 'logic');
        $list = $rechargeLogic->getUnbalancedListInfo($start, $length, $where);
        $returnArr = [];
        $num = 0;
        foreach ($list as $k => $v) {
            $num = $num + 1;
            $sp_name = empty($v['real_name']) ? ((empty($v['company_name']) ? '' : $v['company_name'])) : $v['real_name'];
            $returnArr[] = [
                'id' => $v['id'],//id
                'num' => $num,//num
                'order_code' => $v['order_code'],//订单号
                'policy_code' => $v['policy_code'],//保单号
                'sp_name' => $sp_name,//货主端姓名
                'dr_name' => $v['dr_name'],//司机姓名
                'pay_time_trans' => date('Y-m-d', $v['pay_time']),//交易时间
                'amount' => $v['final_price'],//订单金额
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Order/showdetail', ['id' => $v['id']]) . '" >查看</a> <a class="settle" href="javascript: void(0);"  data-field="is_clear" data-value="1" data-update="' . $v['id'] . '" data-action="' . url('Order/clear', ['id' => $v['id']]) . '">结算</a>'];
        }

        $total = $rechargeLogic->getUnbalancedListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    /**
     * 得到充值记录
     */

    public function getRechargeList() {
        // rt_sp_recharge_order
        $where = [];
        $get = input('param.');
        // var_dump($get);
        //手机号码	真实姓名	用户身份	充值时间	充值路径
        foreach (['phone', 'name', 'type', 'paytime', 'payway'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['name'] = ['like', "%{$get[$key]}%"];
                } else if ($key == 'payway') {
                    $where['pay_way'] = $get[$key];
                } else if ($key == 'paytime') {
                    $where['pay_time'] = array('between', array(strtotime($get[$key]), strtotime($get[$key]) + 86400));
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $rechargeLogic = Model('Recharge', 'logic');
        $listAll = $rechargeLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        $num = 0;
        if (!empty($listAll)) {
            foreach ($listAll as $k => $v) {
                $types = ['person' => '个人货主端', 'company' => '公司货主', 'driver' => '司机端'];
                $payways = [0 => '', 1 => '支付宝', 2 => '微信'];
                $paystatus = [0 => '未支付', 1 => '支付成功', 2 => '支付失败'];
                //  $action = '';
                //手机号码	真实姓名	用户身份	充值时间	充值路径	充值金额	账户余额

                $returnArr[] = [
                    'id' => $v['id'],//id
                    'num' => ++$num,
                    'phone' => $v['phone'],//手机号
                    'name' => $v['name'],//真实姓名
                    'type' => $types[$v['type']],//用户身份
                    'paytime' => empty($v['pay_time']) ? '' : date('Y-m-d H:i', $v['pay_time']),//充值时间
                    'payway' => $payways[$v['pay_way']],//充值路径
                    'amount' => wztxMoney($v['real_amount']),//充值金额 实际支付金额
                    'balance' => wztxMoney($v['balance']),// 账户余额
                    'status' => $paystatus[$v['pay_status']],//状态
                    'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Financial/showdetail', ['id' => $v['id']]) . '" >查看</a>',
                ];
            }
        }

        // var_dump($returnArr);
        $total = $rechargeLogic->getListNum($where);
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
        //return view('edit');
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
        return view();
    }

    //导出表格
    public function exportExcel() {
        // rt_sp_recharge_order
        $where = [];
        $get = input('param.');
        // var_dump($get);
        //手机号码	真实姓名	用户身份	充值时间	充值路径
        foreach (['phone', 'name', 'type', 'paytime', 'payway'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['name'] = ['like', "%{$get[$key]}%"];
                } else if ($key == 'payway') {
                    $where['pay_way'] = $get[$key];
                } else if ($key == 'paytime') {
                    $where['pay_time'] = array('between', array(strtotime($get[$key]), strtotime($get[$key]) + 86400));
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $path = ROOT_PATH . 'public' . DS . 'upload' . DS;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('充值记录导出'); //给当前活动sheet设置名称
        //手机号码	真实姓名	用户身份	充值时间	充值路径	充值金额	账户余额	支付状态
        $PHPSheet->setCellValue('A1', 'ID')->setCellValue('B1', '手机号码');
        $PHPSheet->setCellValue('C1', '真实姓名')->setCellValue('D1', '用户身份');
        $PHPSheet->setCellValue('E1', '充值时间')->setCellValue('F1', '充值路径');
        $PHPSheet->setCellValue('G1', '充值金额')->setCellValue('H1', '账户余额');
        $PHPSheet->setCellValue('I1', '支付状态');

        $num = 1;
        $rechargeLogic = Model('Recharge', 'logic');
        $listAll = $rechargeLogic->getListInfos($where);
        $returnArr = [];
        foreach ($listAll as $k => $v) {
            $types = ['person' => '个人货主端', 'company' => '公司货主', 'driver' => '司机端'];
            $payways = [0 => '', 1 => '支付宝', 2 => '微信'];
            $paystatus = [0 => '未支付', 1 => '支付成功', 2 => '支付失败'];
            //  $action = '';
            //手机号码	真实姓名	用户身份	充值时间	充值路径	充值金额	账户余额
            // var_dump($v);
            $num = $num + 1;
            $PHPSheet->setCellValue('A' . $num, $v['id'])
                ->setCellValue('B' . $num, $v['phone'])
                ->setCellValue('C' . $num, $v['name'])
                ->setCellValue('D' . $num, $types[$v['type']])
                ->setCellValue('E' . $num, empty($v['pay_time']) ? '' : date('Y-m-d H:i', $v['pay_time']))
                ->setCellValue('F' . $num, $payways[$v['pay_way']])
                ->setCellValue('G' . $num, wztxMoney($v['real_amount']))
                ->setCellValue('H' . $num,wztxMoney($v['balance']) )
                ->setCellValue('I' . $num, $paystatus[$v['pay_status']]);
            $PHPSheet ->getStyle('G'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $PHPSheet ->getStyle('H'.$num)->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path . '/recharge.xlsx'); //表示在$path路径下面生成itemList.xlsx文件
        $file_name = "recharge.xlsx";
        $contents = file_get_contents($path . '/recharge.xlsx');
        $file_size = filesize($path . '/recharge.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=" . $file_name);
        exit($contents);
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
