<?php

namespace app\admin\controller;

use service\LogService;
use think\Request;
use service\DataService;
use think\db;

class BlackInfo extends BaseController {
    protected $table = 'BlackList';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
        return view();
    }

    /**
     * 得到黑名单列表
     */

    public function getBlackList() {
        $where = [];
        $get = input('param.');
      /*  //性别 车牌/姓名 省
        // 应用搜索条件
        $auth_statuss = ['init' => '未认证',
            'check' => '认证中',
            'pass' => '认证通过',
            'refuse' => '认证失败',
            'delete' => '后台删除'];*/
        foreach (['is_reg'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'is_reg') {
                    if($get[$key] == 'is'){
                        $where['user_id'] = ['exp', "is not null"];
                    }else if($get[$key] == 'isnot'){
                        $where['user_id'] = ['exp', "is  null"];
                    }
                }
            }

        }
        if(!in_array(input('type'),['person','company','driver','car','phone'])){
            $info = ['draw' => time(), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'extdata' => $where];
            return json($info);
          //  `type` tinyint(1) DEFAULT '0' COMMENT '0=货主端，1=司机端，2=个人货主,3=公司货主，4=司机，5=车辆',
        }
        switch (input('type')){
            case 'person':
                $where['type'] = 2;
                break;
            case 'company':
                $where['type'] = 3;
                break;
            case 'driver':
                $where['type'] = 4;
                break;
            case 'car':
                $where['type'] = 5;
                break;
            case 'phone':
                $where['type'] = ['exp','in (1,0)'];
                break;
        }

        $where['is_del'] = 0;
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $blackLogic = Model('BlackInfo', 'logic');
        $list = $blackLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        foreach ($list as $k => $v) {
            if(in_array($v['type'],[0,1])){
                $number = ($v['type']==1)?'司机端':'货主端';
                $name = $v['phone'];
            }else{
                $number = $v['number'];
                $name = $v['name'];
            }
            //  $action = '';
            $returnArr[] = [
                'id'=>$v['id'],
                'check' => '<input class="list-check-box" value="' . $v['id'] . '" type="checkbox"/>',//id
                'name' => $name,//用户名称
                'number' =>$number,//手机号
                'action' => '<a class="look"  href="javascript:void(0);"  data-field="id" data-value="' . $v['id'] . '" data-update="' . $v['id'] . '" data-action="' . url('BlackInfo/changeStatus', ['id' => $v['id']]) . '"     >拉回白名单</a>',
          //      'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Driver/showdetail', ['id' => $v['a_id']]) . '" >查看</a>',
            ];
        }

        $total = $blackLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    //批量拉回白名单
    public function blackdel(){
        $ids = explode(',', input("id", ''));
        foreach ($ids as $k =>$v){
            $this->changeStatusAll($v);
        }
        $this->success('拉回白名单成功', '');
    }


    public function blackphoneadd() {
        if (request()->isPost()) {
            $data = input('param.');
            $data['phone'] = input('name');
            $data['type'] = input('client_type');
            $data['update_at'] = time();
            $isexist = Db::name('BlackList')->where(['phone'=>$data['phone'],'type'=>$data['type']])->find();
            if($data['type'] == 1){
                $driverLogic = model('Driver', 'logic');
                $detail = $driverLogic->updateStatus(['phone'=>$data['phone']], ['is_black'=>1]);
            }else if($data['type'] == 0){
                $shipperLogic = model('Shipper', 'logic');
                $detail = $shipperLogic->updateStatus(['phone'=>$data['phone']], ['is_black'=>1]);
            }
            if(empty($isexist)){
                $data['create_at'] = time();
                $result = DataService::save('BlackList', $data);
                if ($result !== false) {
                    LogService::write('黑名单添加成功', implode(',', $data));
                    $this->success('恭喜，保存成功哦！', '');
                } else {
                    LogService::write('黑名单添加失败', implode(',', $data));
                    $this->error('保存失败，请稍候再试！', '');
                }
            }else{
                if (  Db::name('BlackList')->where(['phone'=>$data['phone'],'type'=>$data['type']])->update(['is_del'=>0,'update_at'=>time()])){
                    LogService::write('黑名单添加成功', '黑名单添加成功' . input("post.id", ''));
                    $this->success("黑名单添加成功！", '');
                }
                LogService::write('黑名单添加失败', '黑名单添加失败' . input("post.id", ''));
                $this->error("保存失败，请稍候再试！");
            }
            return view();
        } else {
            return view();
        }
    }
    //车型添加
    public function blackadd() {
        //var_dump('1111');
        if (request()->isPost()) {
            $data = input('param.');
            switch (input('typeval')){
                case 'person':
                    $data['type'] = 2;
                    break;
                case 'company':
                    $data['type'] = 3;
                    break;
                case 'driver':
                    $data['type'] = 4;
                    break;
                case 'car':
                    $data['type'] = 5;
                    break;
                default:
                    $this->error('保存失败，请稍候再试！', '');
                    break;
            }
            $data['update_at'] = time();
            $isexist = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->find();
            if(empty($isexist)){
                $data['create_at'] = time();
                $result = DataService::save('BlackList', $data);
                if ($result !== false) {
                    LogService::write('黑名单添加成功', implode(',', $data));
                    $this->success('恭喜，保存成功哦！', '');
                } else {
                    LogService::write('黑名单添加失败', implode(',', $data));
                    $this->error('保存失败，请稍候再试！', '');
                }
            }else{
                if (Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->update(['is_del'=>0,'update_at'=>time()])) {
                    LogService::write('黑名单添加成功', '黑名单添加成功' . input("post.id", ''));
                    $this->success("黑名单添加成功！", '');
                }
                LogService::write('黑名单添加成功', '黑名单添加失败' . input("post.id", ''));
                $this->error("保存失败，请稍候再试！");
            }
            return view();
        } else {
            return view();
        }
    }
    //拉回白名单
    public function changeStatusAll($id='') {
        $data['id'] = $id;
        $isexist = Db::name('BlackList')->where(['id' => $data['id']])->find();

        if (empty($isexist)) {
            return false;
        }
        if (in_array($isexist['type'], [2, 3, 4, 5])) {
            if (Db::name('BlackList')->where(['id' => $data['id'], 'type' => $isexist['type']])->update(['is_del' => 1, 'update_at' => time()])) {
                if (!empty($isexist['user_id'])) {
                    //0=货主端，1=司机端，2=个人货主,3=公司货主，4=司机，5=车辆
                    if (in_array($isexist['type'], [2, 3])) {
                        Db::name('BlackList')->where(['user_id' => $isexist['user_id'], 'type' => ['exp', 'in (0,2,3)']])->update(['is_del' => 1, 'update_at' => time()]);
                        $detail = model('Shipper', 'logic')->updateStatus(['id' => $isexist['user_id']], ['is_black' => 0]);
                    } else if (in_array($isexist['type'], [4, 5])) {
                        Db::name('BlackList')->where(['user_id' => $isexist['user_id'], 'type' => ['exp', 'in (1,4,5)']])->update(['is_del' => 1, 'update_at' => time()]);
                        $detail = model('Driver', 'logic')->updateStatus(['id' => $isexist['user_id']], ['is_black' => 0]);
                    }
                }
                LogService::write('拉回白名单成功', '拉回白名单成功' . $data['id']);
                return false;
            }
            LogService::write('拉回白名单失败', '拉回白名单失败' . $data['id']);
            return false;
        } else {
            Db::name('BlackList')->where(['id' => $data['id'], 'type' => $isexist['type']])->update(['is_del' => 1, 'update_at' => time()]);
            if ($isexist['type'] == 1) {
                $driverLogic = model('Driver', 'logic');
                $detail = $driverLogic->updateStatus(['phone' => $isexist['phone']], ['is_black' => 0]);
            } else if ($isexist['type'] == 0) {
                $shipperLogic = model('Shipper', 'logic');
                $detail = $shipperLogic->updateStatus(['phone' => $isexist['phone']], ['is_black' => 0]);
            }
            LogService::write('拉回白名单成功', '拉回白名单成功' . $data['id']);
            return false;
        }
    }


        //拉回白名单
    public function changeStatus() {
         $data = input('param.');
        $isexist = Db::name('BlackList')->where(['id'=>$data['id']])->find();

        if(empty($isexist)){
            $this->error("保存失败，请稍候再试！");
        }
        if(in_array($isexist['type'],[2,3,4,5]) ){
            if (Db::name('BlackList')->where(['id'=>$data['id'],'type'=>$isexist['type']])->update(['is_del'=>1,'update_at'=>time()])) {
                if(!empty($isexist['user_id'])){
                    //0=货主端，1=司机端，2=个人货主,3=公司货主，4=司机，5=车辆
                    if(in_array($isexist['type'],[2,3])){
                        Db::name('BlackList')->where(['user_id'=>$isexist['user_id'],'type'=>['exp','in (0,2,3)']])->update(['is_del'=>1,'update_at'=>time()]);
                        $detail = model('Shipper', 'logic')->updateStatus(['id'=>$isexist['user_id']], ['is_black'=>0]);
                    }else if(in_array($isexist['type'],[4,5])){
                        Db::name('BlackList')->where(['user_id'=>$isexist['user_id'],'type'=>['exp','in (1,4,5)']])->update(['is_del'=>1,'update_at'=>time()]);
                        $detail = model('Driver', 'logic')->updateStatus(['id'=>$isexist['user_id']], ['is_black'=>0]);
                    }
                }
                LogService::write('拉回白名单成功', '拉回白名单成功' . $data['id']);
                $this->success("拉回白名单成功！", '');
                return;
            }
            LogService::write('拉回白名单失败', '拉回白名单失败' . $data['id']);
            $this->error("保存失败，请稍候再试！");
        }else{
            Db::name('BlackList')->where(['id'=>$data['id'],'type'=>$isexist['type']])->update(['is_del'=>1,'update_at'=>time()]);
            if($isexist['type'] == 1){
                $driverLogic = model('Driver', 'logic');
                $detail = $driverLogic->updateStatus(['phone'=>$isexist['phone']], ['is_black'=>0]);
            }else if($isexist['type'] == 0){
                $shipperLogic = model('Shipper', 'logic');
                $detail = $shipperLogic->updateStatus(['phone'=>$isexist['phone']], ['is_black'=>0]);
            }
            LogService::write('拉回白名单成功', '拉回白名单成功' . $data['id']);
            $this->success("拉回白名单成功！", '');
        }
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
