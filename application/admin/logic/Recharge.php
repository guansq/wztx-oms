<?php

namespace app\admin\logic;

use \think\Db;

class Recharge extends BaseLogic {

    /*
       * 得到充值列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('SpRechargeOrder')->field('*')->limit("$start,$length")->where($where)->order('pay_time desc')->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }


    /*
       * 得到订单列表
       */
    public function getUnbalancedListInfo($start, $length, $where = []) {
        $list = Db::name('TransportOrder')->alias('a')->join('DrBaseInfo b', 'a.dr_id=b.id', 'left')->where($where)->limit("$start,$length")
            ->field('a.*,b.real_name dr_name')->select();
//echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
    /*
       * 得到订单列表
       */
    public function getUnbalancedListNum( $where = []) {
        $list = Db::name('TransportOrder')->alias('a')->join('DrBaseInfo b', 'a.dr_id=b.id', 'left')->where($where)
            ->field('a.*,b.real_name')->count();
//echo $this->getLastSql();
        return $list;
    }
    /*
      * 得到充值列表
      */
    public function getListInfos( $where = []) {
        $list = Db::name('SpRechargeOrder')->field('*')->where($where)->order('pay_time desc')->select();
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
