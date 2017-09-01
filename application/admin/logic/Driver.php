<?php

namespace app\admin\logic;

use \think\Db;
use service\DataService;
class Driver extends BaseLogic {
    /*
     * 得到司机端分页列表
     */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')
            ->join('DrCarinfoAuth b', 'a.car_id=b.id', 'left')->where($where)->limit("$start,$length")
            ->field('*,a.id a_id')->order('a.create_at desc')->select();

        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    /*
     * 修改车型状态
     */
    public function dealCarStyleList($where = [], $status = []) {
        $list = Db::name('CarStyle')->where($where)->update($status);
        // echo $this->getLastSql();
        return $list;
    }

    /*
     * 得到车型列表
     */
    public function getCarList($where = []) {
        $list = Db::name('CarStyle')->field('*')->where($where)->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')->join('DrCarinfoAuth b', 'a.car_id=b.id', 'left')->where($where)
            ->field('*')->count();
        return $list;
    }

    /*
     * 得到司机端基本信息
     */
    public function getDriverInfo($id) {
        $list = Db::name('DrBaseInfo')->where(['id' => $id])->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
     * 得到司机端基本信息
     */
    public function getCarinfoAuth($id) {
        $list = Db::name('DrCarinfoAuth')->where(['id' => $id])->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改司机端认证状态
    function updateStatus($where, $status) {
        $list = Db::name('DrBaseInfo')->where($where)->update($status);
        return $list;
    }


    //删除黑名单
    public function delBlack($blackinfo = []) {
        if (empty($blackinfo)) {
            return false;
        }
        $drdetail = Db::name('DrBaseInfo')->where(['id' => $blackinfo['user_id']])->find();
        if (empty($drdetail)) {
            return false;
        }

        $data['update_at'] = time();
        $data['type'] = 4;
        $data['name'] = $drdetail['real_name'];
        $data['number'] = $drdetail['identity'];
        $data['user_id'] = $blackinfo['user_id'];
        $data['reason'] = $blackinfo['reason'];

        $isexist = Db::name('BlackList')->where(['name' => $data['name'], 'number' => $data['number'], 'type' => $data['type']])->find();
        if (!empty($isexist)) {
            $result = Db::name('BlackList')->where(['name' => $data['name'], 'number' => $data['number'], 'type' => $data['type']])->update(['is_del' => 1, 'update_at' => time()]);
        }

        $dataphone['type'] = 1;
        $dataphone['phone'] = $drdetail['phone'];
        $dataphone['reason'] = $blackinfo['reason'];
        $dataphone['user_id'] = $blackinfo['user_id'];

        $isexist = Db::name('BlackList')->where(['phone' => $dataphone['phone'], 'type' => $dataphone['type']])->find();
        if (!empty($isexist)) {
            $result = Db::name('BlackList')->where(['phone' => $dataphone['phone'], 'type' => $dataphone['type']])->update(['is_del' => 1, 'update_at' => time()]);
        }
        $datacar['update_at'] = time();
        $datacar['type'] = 5;
        $cardetail = Db::name('DrCarinfoAuth')->where(['id' => $drdetail['car_id']])->find();
        if (!empty($cardetail)) {
            $datacar['name'] = $cardetail['card_number'];
            $datacar['number'] = '';
            $datacar['user_id'] = $blackinfo['user_id'];
            $datacar['reason'] = $blackinfo['reason'];

            $isexist = Db::name('BlackList')->where(['name' => $datacar['name'], 'number' => $datacar['number'], 'type' => $datacar['type']])->find();
            if (!empty($isexist)) {
                $result = Db::name('BlackList')->where(['name' => $datacar['name'], 'number' => $datacar['number'], 'type' => $datacar['type']])->update(['is_del' => 1, 'update_at' => time()]);
            }
        }
        return true;
    }

    //添加黑名单
    public function addBlack($blackinfo = []) {
        if (empty($blackinfo)) {
            return false;
        }
        $drdetail = Db::name('DrBaseInfo')->where(['id' => $blackinfo['user_id']])->find();
        if (empty($drdetail)) {
            return false;
        }

        $data['update_at'] = time();
        $data['type'] = 4;
        $data['name'] = $drdetail['real_name'];
        $data['number'] = $drdetail['identity'];
        $data['user_id'] = $blackinfo['user_id'];
        $data['reason'] = $blackinfo['reason'];

        $isexist = Db::name('BlackList')->where(['name' => $data['name'], 'number' => $data['number'], 'type' => $data['type']])->find();
        if (empty($isexist)) {
            $data['create_at'] = time();
            $result = DataService::save('BlackList', $data);
        } else {
            $result = Db::name('BlackList')->where(['name' => $data['name'], 'number' => $data['number'], 'type' => $data['type']])->update(['is_del' => 0, 'update_at' => time(),'user_id'=>$data['user_id']]);
        }

        $dataphone['type'] = 1;
        $dataphone['phone'] = $drdetail['phone'];
        $dataphone['reason'] = $blackinfo['reason'];
        $dataphone['user_id'] = $blackinfo['user_id'];

        $isexist = Db::name('BlackList')->where(['phone' => $dataphone['phone'], 'type' => $dataphone['type']])->find();
        if (empty($isexist)) {
            $dataphone['create_at'] = time();
            $result = DataService::save('BlackList', $dataphone);
        } else {
            $result = Db::name('BlackList')->where(['phone' => $dataphone['phone'], 'type' => $dataphone['type']])->update(['is_del' => 0, 'update_at' => time(),'user_id'=>$dataphone['user_id']]);
        }
        $datacar['update_at'] = time();
        $datacar['type'] = 5;
        $cardetail = Db::name('DrCarinfoAuth')->where(['id' => $drdetail['car_id']])->find();
        if (!empty($cardetail)) {
            $datacar['name'] = $cardetail['card_number'];
            $datacar['number'] = '';
            $datacar['user_id'] = $blackinfo['user_id'];
            $datacar['reason'] = $blackinfo['reason'];

            $isexist = Db::name('BlackList')->where(['name' => $datacar['name'], 'number' => $datacar['number'], 'type' => $datacar['type']])->find();
            if (empty($isexist)) {
                $datacar['create_at'] = time();
                $result = DataService::save('BlackList', $datacar);
            } else {
                $result = Db::name('BlackList')->where(['name' => $datacar['name'], 'number' => $datacar['number'], 'type' => $datacar['type']])->update(['is_del' => 0, 'update_at' => time(),'user_id'=>$datacar['user_id']]);
            }
        }
        return true;
    }

    //修改司机端黑名单状态
    public function updateBlackStatus($isblack, $blackinfo) {
        if ($isblack == 1) {
           return $this->addBlack($blackinfo);
        } elseif ($isblack == 2) {
            return $this->delBlack($blackinfo);
        }
    }
    //通过sp_id获得系统表中id
    public function getSystemDriverIds($where = []) {
        $list = Db::name('DrBaseInfo')->where($where)->field('user_id')->select();
        return $list;
    }

    //通过sp_id删除基本信息表
    public function delDrBaseInfoIds($where = []) {
        $list = Db::name('DrBaseInfo')->where($where)->delete();
        return $list;
    }

    //删除系统表中货主信息
    public function delSystemDriverIds($where = []) {
        $list = Db::name('SystemUserDriver')->where($where)->delete();
        return $list;
    }

    //获取截止日期超出当前时间的货主id
    public function getReauthListIds($where = []) {
        $list = Db::name('DrCarinfoAuth')->where($where)->field(['dr_id'])->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
    * 得到某种状态的数量
   */
    public function getListTotalNum($where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')->where($where)->count();
        return $list;
    }
}
