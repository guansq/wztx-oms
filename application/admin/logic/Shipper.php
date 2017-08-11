<?php

namespace app\admin\logic;

use \think\Db;

class Shipper extends BaseLogic {
    /*
     * 得到货主端分页列表
     */
    public function getListInfo($start, $length, $where = []) {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')
                ->where($where)->limit("$start,$length")->field('*')->order('create_at desc')->select();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)->limit("$start,$length")
                ->field('a.*,b.com_name,b.phone companyphone,b.address,a.phone moblie')->order('a.create_at desc')->select();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = []) {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')
                ->where($where)->field('*')->count();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)
                ->field('a.*,b.com_name,b.phone companyphone,b.address')->count();
        }
        return $list;
    }

    /*
     * 得到货主端认证信息
     */
    public function getShipperdetail($where = []) {
        if ($where['type'] == 'person') {
            $list = Db::name('SpBaseInfo')->alias('a')
                ->where($where)->field('*')->select();
            //  echo $this->getLastSql();
        } else if ($where['type'] == 'company') {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)
                ->field('a.*,a.back_pic person_back_pic,a.hold_pic person_hold_pic,a.front_pic person_front_pic,b.*,b.back_pic com_back_pic,b.front_pic com_front_pic,b.identity com_identity,a.identity person_identity,b.hold_pic com_hold_pic')->select();
          // echo $this->getLastSql();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改货主端认证状态
    public function updateStatus($where, $status) {
        $list = Db::name('SpBaseInfo')->where($where)->update($status);
        return $list;
    }

    //修改货主端黑名单状态
    public function updateBlackStatus($isblack, $blackinfo) {
        $where = ['type' => $blackinfo['type'], 'user_id' => $blackinfo['user_id']];
        $list = Db::name('BlackList')->where($where)->select();
        if ($isblack == 1) {
            $blackinfo['is_del'] = 0;
        } elseif ($isblack == 2) {
            $blackinfo['is_del'] = 1;
        }
        if (empty($list)) {
            $blackinfo['create_at'] = time();
            $result = Db::name('BlackList')->insert($blackinfo);
        } else {
            $blackinfo['update_at'] = time();
            $result = Db::name('BlackList')->where($where)->update($blackinfo);
        }
        return $result;
    }

    //通过sp_id获得系统表中id
    public function getSystemShipperIds($where = []) {
        $list = Db::name('SpBaseInfo')->where($where)->field('user_id')->select();
        return $list;
    }
    //通过sp_id删除基本信息表
    public function delSpBaseInfoIds($where = []) {
        $list = Db::name('SpBaseInfo')->where($where)->delete();
        return $list;
    }
    //删除系统表中货主信息
    public function delSystemShipperIds($where = []) {
        $list = Db::name('SystemUserShipper')->where($where)->delete();
        return $list;
    }

    /*
     * 得到某种状态的数量
    */
    public function getListTotalNum($where = []) {
        $list = Db::name('SpBaseInfo')->alias('a')->where($where)->count();
        //echo $this->getLastSql();
        return $list;
    }
}
