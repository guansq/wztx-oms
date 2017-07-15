<?php

namespace app\admin\logic;

use \think\Db;

class Withdraw extends BaseLogic {

    /*
       * 得到提现列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('Withdraw')->field('*')->limit("$start,$length")->where($where)->order('create_at desc')->select();
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

        $list = Db::name('Withdraw')->where($where)->field('*')->select();
        // echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('Withdraw')->field('*')->where($where)->count();
        return $list;
    }
    //提现状态
    function updateStatus($where, $status)
    {
        $list = Db::name('Withdraw')->where($where)->update($status);
        return $list;
    }

}
