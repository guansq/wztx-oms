<?php

namespace app\admin\controller;

use service\LogService;
use think\Request;
use service\DataService;
use think\db;
class Driver extends BaseController {
    protected $table = 'DrBaseInfo';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
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

    public function getSpList() {
        $where = [];
        $get = input('param.');
        //性别 车牌/姓名 省
        // 应用搜索条件
        foreach (['name', 'sex', 'province'] as $key) {
            $get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'province') {
                    $where['a.address'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'name') {
                    $where['a.real_name|b.card_number'] = ['like', "%{$get[$key]}%"];
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
                $sexname = '未知';
            } elseif ($v['sex'] === 1) {
                $sexname = ' 男';
            } elseif ($v['sex'] === 2) {
                $sexname = '女';
            }
            //  $action = '';
            $returnArr[] = [
                'id' => $v['id'],//id
                'name' => $v['real_name'],//用户名称
                'phone' => $v['phone'],//手机号
                'sexname' => $sexname,//性别
                'number' => $v['identity'],//身份证号
                'address' => $v['address'],//地址
                'cardnumber' => $v['card_number'],//车牌
                'logisticstype' => $logisticstypes[$v['logistic_stype']], //物流
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
    public function showdetail($id) {
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
        $sexname = '';
        if ($item[0]['sex'] === 0) {
            $sexname = '未知';
        } elseif ($item[0]['sex'] === 1) {
            $sexname = ' 男';
        } elseif ($item[0]['sex'] === 2) {
            $sexname = '女';
        }
        $returnArr['name'] = $item[0]['real_name'];
        $returnArr['sexname'] = $sexname;
        $returnArr['number'] = $item[0]['identity'];
        $returnArr['address'] = $item[0]['address'];
        $logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
        $returnArr['logisticstype'] = $logisticstypes[$item[0]['logistic_stype']];
        $returnArr['personholdpic'] = $item[0]['hold_pic'];
        $returnArr['personbackpic'] = $item[0]['back_pic'];
        $returnArr['personfrontpic'] = $item[0]['front_pic'];

        $carauth = $driverLogic->getCarinfoAuth($item[0]['id']);
        if (!empty($carauth)) {
            $carstyle = $driverLogic->getCarList();
            $carstylearray = [];
            foreach ($carstyle as $key => $item) {
                $carstylearray[$item['id']] = array('name' => $item['name'], 'type' => $item['type']);
            }
            $returnArr['carlengthname'] = $carstylearray[$carauth[0]['car_style_length_id']]['name'];
            $returnArr['carstylename'] = $carstylearray[$carauth[0]['car_style_type_id']]['name'];
            $returnArr['weight'] = $carauth[0]['weight'];
            $returnArr['volume'] = $carauth[0]['volume'];
            $returnArr['cardnumber'] = $carauth[0]['card_number'];
            $returnArr['policydeadline'] = $carauth[0]['policy_deadline'];
            $returnArr['licensedeadline'] = $carauth[0]['license_deadline'];
            $returnArr['indexpic'] = $carauth[0]['index_pic'];
            $returnArr['vehiclelicensepic'] = $carauth[0]['vehicle_license_pic'];
            $returnArr['drivinglicencepic'] = $carauth[0]['driving_licence_pic'];
            $returnArr['insurancepolicypic'] = $carauth[0]['insurance_policy_pic'];
            $returnArr['operationpic'] = $carauth[0]['operation_pic'];
        }
        //var_dump($returnArr);
        $this->assign('id', $id);
        $this->assign('item', $returnArr);

        return view('edit');
    }

    //审核通过
    public function pass() {
        $id = input('id');
        $driverLogic = model('Driver', 'logic');
        $status = ['auth_status' => 'pass', 'update_at' => time()];
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

    public function auth() {
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
                $status['auth_status'] = 'refuse';
                $tmp = $titile;// . ',' . time();
                $where['auth_status'] = 'init';
                $status['auth_info'] = ['exp', 'concat(IFNULL(auth_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'frozen': //冻结账户
                $where['bond_status'] = 'checked';
                $status['bond_status'] = 'frozen';
                $tmp = $titile;// . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'unfrozen': //取消冻结
                $where['bond_status'] = 'frozen';
                $status['bond_status'] = 'checked';
                $tmp = $titile;// . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'black': //加入黑名单
                $where['is_black'] = '0';
                $status['is_black'] = '1';
                $tmp = $titile;//. ',' . time();
                $isblack = 1;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            case 'unblack': //从黑名单删除
                $where['is_black'] = '1';
                $status['is_black'] = '0';
                $tmp = $titile;// . ',' . time();
                $isblack = 2;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            default:
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        if (in_array($isblack, [1, 2])) {
            $blackinfo = ['user_id' => $id, 'phone' => input('phone'), 'reason' => ['exp', 'concat(IFNULL(reason,\'\'),\'' . '-' . $tmp . '\')'], 'type' => '1',];
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

    //获取司机抢单范围
    public function range() {
        return view();
    }

    //车型设置
    public function carstyle() {
        $driverLogic = Model('Driver', 'logic');
        $where = ['type' => 1];
        $carstyle = $driverLogic->getCarList($where);
        $this->assign('carstyle', $carstyle);
        return view();
    }

    //车型添加
    public function carstyleadd() {
        //var_dump('1111');
        if(request()->isPost()) {
            $data = input('param.');
            $data['type'] = 1;
            $result = DataService::save('CarStyle', $data);
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
            return view();
        }else{
//           / var_dump('222');
            return view();
        }
    }
public function carlengthdel(){
    $driverLogic = Model('Driver', 'logic');
    $where = ['id'=>input('id')];
    $status = ['status' => input('status')];
    $dealcarstyle = $driverLogic->dealCarStyleList($where, $status);
    if ($dealcarstyle) {
        return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
    } else {
        return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
    }
}

    //车型删除
    public function carstyledel() {
        $data = input('param.');
        $data['status'] = 1;
        $dealcarstyle = '';
        $driverLogic = Model('Driver', 'logic');
        $allid = substr($data['allid'], 1);

        $where = ['id' => ['exp', ' IN (' . $allid . ')'], 'type' => 1];
        $dealcarstyle = $driverLogic->dealCarStyleList(['type' => 1], ['status' => 1]);
        $dealcarstyle = $driverLogic->dealCarStyleList($where, ['status' => 0]);
        if ($dealcarstyle) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    //车长设置
    public function carlength() {
        $db = Db::name('CarStyle');
        $list =  $db->field('*')->where(['type'=>2])->select();
        //var_dump($list);
        $this->assign('list',$list  );
        return view();
    }
    //车长设置
    public function carlengthadd() {
        if(request()->isPost()){
            $data=input('param.');
            $result = DataService::save('CarStyle', $data);//Db::name($this->table)->allowField(true)->insert($data);
          //  LogService::write('Banner管理', '上传Banner成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            $id = input('id');
            if(!empty($id)){
                $db = Db::name('CarStyle');
                $list =  $db->field('*')->where(['type'=>2,'id'=>$id])->select();
                $this->assign('vo',$list[0]);
            }else{
                $this->assign('vo','');
            }

            return view();
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
    public function read() {
        //
        //  return view('edit');
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
