<?php

namespace app\admin\controller;
use service\LogService;
use think\Request;

class Driver extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $provincelist = ['北京', '天津', '河北', '山西', '内蒙古', '辽宁', '吉林', '黑龙江',
            '上海', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南',
            '广东', '广西', '海南', '重庆', '四川', '贵州', '云南', '西藏', '陕西', '甘肃',
            '青海', '宁夏', '新疆', '台湾', '香港', '澳门', '钓鱼岛'];
        $this->assign('provincelist', $provincelist);
        return view();
    }

    /**
     * 得到司机列表
     */

    public function getSpList()
    {
        $where = [];
        $get = input('param.');
        //性别 车牌/姓名 省
        // 应用搜索条件
        foreach (['name', 'sex', 'province'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'province') {
                    $where['b.address'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'name') {
                    $where['b.name|c.cardnumber'] = ['like', "%{$get[$key]}%"];
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $driverLogic = Model('Driver', 'logic');
        $list = $driverLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        foreach ($list as $k => $v) {
            $logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
            $sexname = '';
            if ($v['sex'] === 0) {
                $sexname = '男';
            } elseif ($v['sex'] === 1) {
                $sexname = '女';
            }
            //  $action = '';
            $returnArr[] = [
                'id' => $v['id'],//id
                'name' => $v['name'],//用户名称
                'phone' => $v['phone'],//手机号
                'sexname' => $sexname,//性别
                'number' => $v['number'],//身份证号
                'address' => $v['address'],//地址
                'cardnumber' => $v['cardnumber'],//车牌
                'logisticstype' => $logisticstypes[$v['logisticstype']], //物流
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Driver/showdetail', ['id' => $v['id']]) . '" >查看</a>',
            ];
        }

        $total = $driverLogic->getListNum($where);
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
        $driverLogic = Model('Driver', 'logic');
        $item = $driverLogic->getDriverInfo($id);
        if (empty($item)) {
            $this->error('未查询到当前用户信息', '');
        }
        $returnArr = [];
        $returnArr = $item[0];
        $personauth = $driverLogic->getPersonAuth($id);
        if (!empty($personauth)) {
            $returnArr['name'] = $personauth[0]['name'];
            $sexname = '';
            if ($personauth[0]['sex'] === 0) {
                $sexname = '男';
            } elseif ($personauth[0]['sex'] === 1) {
                $sexname = '女';
            }
            $returnArr['sexname'] = $sexname;
            $returnArr['number'] = $personauth[0]['number'];
            $returnArr['address'] = $personauth[0]['address'];
            $logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
            $returnArr['logisticstype'] = $logisticstypes[$personauth[0]['logisticstype']];
            $returnArr['personholdpic'] = $personauth[0]['holdpic'];
            $returnArr['personbackpic'] = $personauth[0]['backpic'];
            $returnArr['personfrontpic'] = $personauth[0]['frontpic'];
        }
        $carauth = $driverLogic->getCarinfoAuth($personauth[0]['id']);
        if (!empty($carauth)) {
            $carstyle = $driverLogic->getCarList();
            $carstylearray = [];
            foreach ($carstyle as $key => $item) {
                $carstylearray[$item['id']] = array('name' => $item['name'], 'type' => $item['type']);
            }
            $returnArr['carlengthname'] = $carstylearray[$carauth[0]['carlength']]['name'];
            $returnArr['carstylename'] = $carstylearray[$carauth[0]['carstyle']]['name'];
            $returnArr['weight'] = $carauth[0]['weight'];
            $returnArr['volume'] = $carauth[0]['volume'];
            $returnArr['cardnumber'] = $carauth[0]['cardnumber'];
            $returnArr['policydeadline'] = $carauth[0]['policydeadline'];
            $returnArr['licensedeadline'] = $carauth[0]['licensedeadline'];
            $returnArr['indexpic'] = $carauth[0]['indexpic'];
            $returnArr['vehiclelicensepic'] = $carauth[0]['vehiclelicensepic'];
            $returnArr['drivinglicencepic'] = $carauth[0]['drivinglicencepic'];
            $returnArr['insurancepolicypic'] = $carauth[0]['insurancepolicypic'];
            $returnArr['operationpic'] = $carauth[0]['operationpic'];
        }
        //var_dump($returnArr);
        $this->assign('id', $id);
        $this->assign('item', $returnArr);

        return view('edit');
    }

    //审核通过
    public function pass()
    {
        $id = input('id');
        $driverLogic = model('Driver', 'logic');
        $status = ['auth_state' => 'pass', 'update_at' => time()];
        $detail = $driverLogic->updateStatus(['id' => $id], $status);
        // session('user', $user);
        LogService::write('司机端:' . $id, '审核通过');
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }

    public function auth()
    {
        $titile = input('title');
        $id = input('id');
        $authtype = input('type');
        $driverLogic = model('Driver', 'logic');
        $status = ['update_at' => time()];
        $where = ['id' => $id];
        $tmp = '';
        $isblack = 0; //1加入黑名单2移除黑名单
        if (empty($titile)) {
            return json(['code' => 4000, 'msg' => '输入文本不能为空', 'data' => []]);
        }
        switch ($authtype) {
            case 'refuse': //拒绝审核
                $status['auth_state'] = 'refuse';
                $tmp = $titile . ',' . time();
                $where['auth_state'] = 'init';
                $status['auth_info'] = ['exp', 'concat(IFNULL(auth_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'frozen': //冻结账户
                $where['bond_state'] = 'checked';
                $status['bond_state'] = 'frozen';
                $tmp = $titile . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'unfrozen': //取消冻结
                $where['bond_state'] = 'frozen';
                $status['bond_state'] = 'checked';
                $tmp = $titile . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'black': //加入黑名单
                $where['is_black'] = '0';
                $status['is_black'] = '1';
                $tmp = $titile . ',' . time();
                $isblack = 1;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            case 'unblack': //从黑名单删除
                $where['is_black'] = '1';
                $status['is_black'] = '0';
                $tmp = $titile . ',' . time();
                $isblack = 2;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            default:
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        if (in_array($isblack, [1, 2])) {
            $blackinfo = ['baseid' => $id, 'phone' => input('phone'), 'reason' => ['exp', 'concat(IFNULL(reason,\'\'),\'' . '-' . $tmp . '\')'], 'type' => '1',];
            $detail = $driverLogic->updateBlackStatus($isblack, $blackinfo);
            //修改黑名单记录表
            if (empty($detail)) {
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
            }
        }
        LogService::write('司机端:' . $id, $authtype . ',' . $titile . ',' . time());
        // 'contract' => ['exp', 'concat(IFNULL(contract,\'\'),\''.','.$src.'\')']
        $detail = $driverLogic->updateStatus($where, $status);
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
        return view('edit');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
