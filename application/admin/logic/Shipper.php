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
                ->where($where)->limit("$start,$length")->field('*')->select();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)->limit("$start,$length")
                ->field('a.*,b.com_short_name,b.phone companyphone,b.address')->select();
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
                ->field('a.*,b.com_short_name,b.phone companyphone,b.address')->count();
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
                ->field('a.*,a.back_pic person_back_pic,a.front_pic person_front_pic,b.*,b.back_pic com_back_pic,b.front_pic com_front_pic,b.identity com_identity,a.identity person_identity')->select();
         //  echo $this->getLastSql();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改货主端认证状态
    function updateStatus($where, $status) {
        $list = Db::name('SpBaseInfo')->where($where)->update($status);
        return $list;
    }

    //修改货主端黑名单状态
    function updateBlackStatus($isblack, $blackinfo) {
        $where = ['type' => $blackinfo['type'], 'user_id' => $blackinfo['user_id']];
        $list = Db::name('BlackList')->where($where)->select();
        if ($isblack == 1) {
            $blackinfo['is_del'] = 0;
            if (empty($list)) {
                $blackinfo['create_at'] = time();
                $result = Db::name('BlackList')->insert($blackinfo);
            } else {
                $blackinfo['update_at'] = time();
                $result = Db::name('BlackList')->where($where)->update($blackinfo);
            }
        } elseif ($isblack == 2) {
            $blackinfo['is_del'] = 1;
            if (empty($list)) {
                $blackinfo['create_at'] = time();
                $result = Db::name('BlackList')->insert($blackinfo);
            } else {
                $blackinfo['update_at'] = time();
                $result = Db::name('BlackList')->where($where)->update($blackinfo);
            }
        }
        return $result;
    }
}
