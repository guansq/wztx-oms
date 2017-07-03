<?php

namespace app\admin\logic;

use \think\Db;

class Withdraw extends BaseLogic {

    /*
       * 得到提现列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('Withdraw')->field('*')->limit("$start,$length")->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    /*
       * 得到提现分页列表
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
        $list = Db::name('Withdraw')->field('shareid,type')->group('shareid and type')->where($where)->count();
        return $list;
    }
}
