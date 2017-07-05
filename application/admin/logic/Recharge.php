<?php

namespace app\admin\logic;

use \think\Db;

class Recharge extends BaseLogic {

    /*
       * 得到充值列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('SpRechargeOrder')->field('*')->limit("$start,$length")->where($where)->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
    /*
      * 得到充值列表
      */
    public function getListInfos( $where = []) {
        $list = Db::name('SpRechargeOrder')->field('*')->where($where)->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    /*
       * 详情页面
       */
    public function getListItem($where = []) {

        $list = Db::name('SpRechargeOrder')->where($where)->field('*')->select();
        // echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('SpRechargeOrder')->field('*')->where($where)->count();
        return $list;
    }

}
