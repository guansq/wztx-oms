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
        if(input('auth_status')){
            $this->assign('auth_status', input('auth_status'));
        }else{
            $this->assign('auth_status', 'all');
        }
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
        $auth_statuss = ['init' => '未认证',
            'check' => '认证中',
            'pass' => '认证通过',
            'refuse' => '认证失败',
            'delete' => '后台删除'];
        foreach (['name', 'sex', 'province', 'auth_status'] as $key) {
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
                'id' => $v['a_id'],//id
                'name' => $v['real_name'],//用户名称
                'phone' => $v['phone'],//手机号
                'sexname' => $sexname,//性别
                'number' => $v['identity'],//身份证号
                'address' => $v['address'],//地址
                'auth_status' => $auth_statuss[$v['auth_status']],//认证状态
                'cardnumber' => $v['card_number'],//车牌
                'logisticstype' => $logisticstypes[$v['logistics_type']], //物流
                'check' => '<input class="list-check-box" value="' . $v['a_id'] . '" type="checkbox"/>',//id
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Driver/showdetail', ['id' => $v['a_id']]) . '" >查看</a>',
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
        $returnArr['logisticstype'] = $logisticstypes[$item[0]['logistics_type']];
        $returnArr['personholdpic'] = $item[0]['hold_pic'];
        $returnArr['personbackpic'] = $item[0]['back_pic'];
        $returnArr['personfrontpic'] = $item[0]['front_pic'];

        $carauth = $driverLogic->getCarinfoAuth($item[0]['car_id']);
        if (!empty($carauth)) {
            foreach ($carauth[0] as $key => $v) {
                $returnArr[$key] = $v;
            }
            // var_dump($carauth);
            /*$carstyle = $driverLogic->getCarList();
            $carstylearray = [];
            foreach ($carstyle as $key => $item) {
                $carstylearray[$item['id']] = array('name' => $item['name'], 'type' => $item['type']);
            }
            $returnArr['carlengthname'] = $carstylearray[$carauth[0]['car_style_length_id']]['name'];
            $returnArr['carstylename'] = $carstylearray[$carauth[0]['car_style_type_id']]['name'];*/
            //$returnArr[] = $carauth[0];
        }
        // var_dump($returnArr);
        $this->assign('id', $id);
        $this->assign('item', $returnArr);

        return view('edit');
    }

    //审核通过
    public function pass() {
        $id = input('id');
        $driverLogic = model('Driver', 'logic');
        $status = ['auth_status' => 'pass', 'update_at' => time(), 'pass_time' => time()];
        $detail = $driverLogic->updateStatus(['id' => $id], $status);
        if ($detail) {
            LogService::write('司机端:' . $id, '审核通过');
            $push_token = getDrPushToken($id);
            if (!empty($push_token)) {
                $titlepush = '认证信息审核通过';
                $contentpush = '您的认证信息审核通过';
                sendMsg($id, $titlepush, $contentpush, 1);
                pushInfo($push_token, $titlepush, $contentpush, 'wztx_driver');//推送给司机
            }
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write('司机端:' . $id, '更新失败');
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
                $where['auth_status'] = 'check';
                $titlepush = '认证信息审核被拒绝';
                $contentpush = '您的认证信息审核被拒绝,拒绝原因:' . $titile;
                //  $status['auth_info'] = ['exp', 'concat(IFNULL(auth_info,\'\'),\'' . '-' . $tmp . '\')'];
                $status['auth_info'] = $tmp;
                break;
            case 'frozen': //冻结账户
                $where['bond_status'] = 'init';
                $status['bond_status'] = 'frozen';
                $status['is_frozen'] = '1';
                $tmp = $titile;// . ',' . time();
                $titlepush = '账户信息被冻结';
                $contentpush = '您的账户信息被冻结,冻结原因:' . $titile;
                //  $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                $status['frozen_info'] = $tmp;
                break;
            case 'unfrozen': //取消冻结
                $where['bond_status'] = 'frozen';
                $status['bond_status'] = 'init';
                $status['is_frozen'] = '0';
                $tmp = $titile;// . ',' . time();
                $titlepush = '账户信息取消冻结';
                $contentpush = '您的账户信息取消冻结,取消冻结:' . $titile;
                //  $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                $status['frozen_info'] = $tmp;
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
        //['exp', 'concat(IFNULL(reason,\'\'),\'' . '-' . $tmp . '\')']
        if (in_array($isblack, [1, 2])) {
            $blackinfo = ['user_id' => $id, 'phone' => input('phone'), 'reason' =>$tmp , 'type' => '1',];
            $detail = $driverLogic->updateBlackStatus($isblack, $blackinfo);
            //修改黑名单记录表
            if (empty($detail)) {
                LogService::write('司机端:' . $id, input('phone') . ',' . $tmp . ',' . time() . ',' . $authtype . ',更新失败');
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
            }
        }

        $detail = $driverLogic->updateStatus($where, $status);
        if ($detail) {
            LogService::write('司机端:' . $id, $authtype . ',' . $titile . ',' . time() . '更新成功');
            $push_token = getDrPushToken($id);
            if (!empty($push_token) && !empty($titlepush) && !empty($contentpush)) {
                sendMsg($id, $titlepush, $contentpush, 1);
                pushInfo($push_token, $titlepush, $contentpush, 'wztx_driver');//推送给司机
            }
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write('司机端:' . $id, $authtype . ',' . $titile . ',' . time() . '更新失败');
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    //获取司机抢单范围
    public function range() {
        return view();
    }

    //车型设置
    public function carstyle() {
        $db = Db::name('CarStyle');
        # 列表排序默认处理
        if ($this->request->isPost() && $this->request->post('action') === 'resort') {
            $data = $this->request->post();
            unset($data['action']);
            foreach ($data as $key => &$value) {
                if (false === $db->where('id', intval(ltrim($key, '_')))->update(['sort' => $value])) {
                    $this->error('列表排序失败，请稍候再试！');
                }
            }
            $this->success('列表排序成功，正在刷新列表！', '');
        }

        $list = $db->field('*')->where(['type' => 1])->order('sort')->select();
        $this->assign('list', $list);
        return view();

//        $driverLogic = Model('Driver', 'logic');
//        $where = ['type' => 1];
//        $carstyle = $driverLogic->getCarList($where);
//        $this->assign('carstyle', $carstyle);
//        return view();
    }

    //车型添加
    public function carstyleadd() {
        //var_dump('1111');
        if (request()->isPost()) {
            $data = input('param.');
            $data['type'] = 1;
            $data['create_at'] = time();
            $data['update_at'] = time();
            $result = DataService::save('CarStyle', $data);
            if ($result !== false) {
                LogService::write('车型添加成功', implode(',', input('param.')));
                $this->success('恭喜，保存成功哦！', '');
            } else {
                LogService::write('车型添加失败', implode(',', input('param.')));
                $this->error('保存失败，请稍候再试！', '');
            }
            return view();
        } else {
            return view();
        }
    }

    //车长状态修改
    public function carlengthdel() {
        $table = 'CarStyle';
        if (DataService::update($table)) {
            LogService::write('车长状态修改', '车长状态修改成功' . input("post.id", ''));
            $this->success("车长状态修改成功！", '');
        }
        LogService::write('车长状态修改', '车长状态修改失败' . input("post.id", ''));
        $this->error("车长状态修改失败，请稍候再试！");
    }

    //车长状态修改
    public function carstyledel() {
        $table = 'CarStyle';
        if (DataService::update($table)) {
            LogService::write('车型状态修改', '车型状态修改成功' . input("post.id", ''));
            $this->success("车型状态修改成功！", '');
        }
        LogService::write('车型状态修改', '车型状态修改失败' . input("post.id", ''));
        $this->error("车型状态修改失败，请稍候再试！");
    }
    //车型状态修改
    /*public function carstyledel() {
        $data = input('param.');
        $data['status'] = 1;
        $dealcarstyle = '';
        $driverLogic = Model('Driver', 'logic');
        $allid = substr($data['allid'], 1);

        $where = ['id' => ['exp', ' IN (' . $allid . ')'], 'type' => 1];
        $dealcarstyle = $driverLogic->dealCarStyleList(['type' => 1], ['status' => 1]);
        $dealcarstyle = $driverLogic->dealCarStyleList($where, ['status' => 0]);

        if ($dealcarstyle) {
            LogService::write('车型状态修改','车型状态修改成功'.$allid);
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            LogService::write('车型状态修改','车型状态修改失败'.$allid);
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }*/

    //车长设置
    public function carlength() {
        $db = Db::name('CarStyle');
        # 列表排序默认处理
        if ($this->request->isPost() && $this->request->post('action') === 'resort') {
            $data = $this->request->post();
            unset($data['action']);
            foreach ($data as $key => &$value) {
                if (false === $db->where('id', intval(ltrim($key, '_')))->update(['sort' => $value])) {
                    $this->error('列表排序失败，请稍候再试！');
                }
            }
            $this->success('列表排序成功，正在刷新列表！', '');
        }

        $list = $db->field('*')->where(['type' => 2])->order('sort')->select();
        $this->assign('list', $list);
        return view();
    }

    //车长设置
    public function carlengthadd() {
        if (request()->isPost()) {
            $data = input('param.');
            $data['create_at'] = time();
            $data['update_at'] = time();
            $result = DataService::save('CarStyle', $data);
            if ($result !== false) {
                LogService::write('车长设置添加', implode(',', input('param.')) . '恭喜，保存成功哦！');
                $this->success('恭喜，保存成功哦！', '');
            } else {
                LogService::write('车长设置添加', implode(',', input('param.')) . '保存失败');
                $this->error('保存失败，请稍候再试！');
            }
        } else {
            $id = input('id');
            if (!empty($id)) {
                $db = Db::name('CarStyle');
                $list = $db->field('*')->where(['type' => 2, 'id' => $id])->select();
                $this->assign('vo', $list[0]);
            } else {
                $this->assign('vo', '');
            }

            return view();
        }
    }

    //重新认证
    public function reauth() {

        $driverLogic = Model('Driver', 'logic');
        // 应用搜索条件
        $time_now = time();
        $where['policy_deadline|license_deadline|operation_deadline|driving_deadline'] = array('elt', $time_now);
        $wheredrids = $driverLogic->getReauthListIds($where);
        $dr_id = [];

        if (!empty($wheredrids)) {
            foreach ($wheredrids as $wheredrid => $item) {
                $dr_id[] = $item['dr_id'];
            }
            LogService::write('司机端重新认证-系统', implode(',', $dr_id));
            $status = ['auth_status' => 'init', 'pass_time' => '', 'update_at' => time()];
            $result = $driverLogic->updateStatus(['id' => ['exp', 'in (' . implode(',', $dr_id) . ')']], $status);
            echo $result;
        } else {
            echo 'no change';
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
    //删除司机端基本信息表和系统表
    public function delete($id) {
        $ids = explode(',', input("id", ''));
        $where["id"] = ['exp', 'in (' . input('id') . ')'];
        $driverLogic = Model('Driver', 'logic');
        $systemShipperIds = $driverLogic->getSystemDriverIds($where);
        $drids = [];
        foreach ($systemShipperIds as $key => $vo) {
            $drids[] = $vo['user_id'];
        }
        $flag = 0;
        // 启动事务
        Db::startTrans();
        try {
            $resultSS = $driverLogic->delSystemDriverIds(["id" => ['exp', 'in (' . implode(',', $drids) . ')']]);
            $resultSBI = $driverLogic->delDrBaseInfoIds($where);
            Db::name('BlackList')->where(['user_id'=>['exp', 'in (' . implode(',', $drids) . ')'],'type'=>['exp','in (1,4,5)']])->update(['is_del'=>1,'update_at'=>time()]);

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $flag = 1;
            Db::rollback();
        }
        if (!$flag) {
            LogService::write('删除司机端基本信息表和系统表成功', implode(',', $drids));
            $this->success('用户信息删除成功', '');
        } else {
            LogService::write('删除司机端基本信息表和系统表失败', implode(',', $drids));
            $this->error('用户信息删除失败', '');
        }
    }
}
