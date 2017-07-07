<?php

namespace app\admin\logic;

use \think\Db;

class Driver extends BaseLogic {

    /*
       * 得到司机端分页列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')
            ->join('DrCarinfoAuth b', 'a.id=b.dr_id', 'left')->where($where)->limit("$start,$length")
            ->field('*')->select();

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
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }


    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')->join('DrCarinfoAuth b', 'a.id=b.dr_id', 'left')->where($where)
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
        $list = Db::name('DrCarinfoAuth')->where(['dr_id' => $id])->field('*')->select();
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
