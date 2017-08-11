<?php

namespace app\admin\logic;

use \think\Db;

class Order extends BaseLogic {

    /*
       * 得到订单列表
       */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('TransportOrder')->alias('a')->join('DrBaseInfo b', 'a.dr_id=b.id', 'left')->join('DrCarinfoAuth c', 'b.car_id=c.id', 'left')->where($where)->limit("$start,$length")
            ->field('a.*,c.card_number')->order('a.create_at desc')->select();
//echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    /*
       * 得到订单成功列表
      */
    public function getSuccessListInfo($where = []) {
        $list = Db::name('TransportOrder')->alias('a')->where($where)->field(' FROM_UNIXTIME(pay_time,"%Y-%m-%d") days,count(id) order_amount,sum(final_price) tran_total')->group('days')->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
       * 得到通过id获取订单详情
       */
    public function getListOneInfo($where = []) {
        $list = Db::name('TransportOrder')->alias('a')->where($where)->field('a.*')->find();
        return $list;
    }

    /*
          * 得到通过id获取地址详情
          */
    public function getAddressInfo($where = []) {
        $list = Db::name('Address')->alias('a')->where($where)->field('a.*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
              * 得到通过dr_id获取车辆详情
              */
    public function getCardInfo($where = []) {
        $list = Db::name('DrBaseInfo')->alias('a')->join('DrCarinfoAuth b', 'a.car_id=b.id', 'left')->where($where)
            ->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改凭证状态
    function updateStatus($where, $status) {
        $list = Db::name('TransportOrder')->where($where)->update($status);
        //echo $this->getLastSql();
        return $list;
    }


    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('TransportOrder')->alias('a')->join('DrBaseInfo b', 'a.dr_id=b.id', 'left')->join('DrCarinfoAuth c', 'b.car_id=c.id', 'left')->where($where)
            ->field('a.*,c.card_number')->order('a.create_at desc')->count();
        return $list;
    }

    /*
      * 得到订单数量
     */
    public function getListTotalNum($where = []) {
        $list = Db::name('TransportOrder')->alias('a')->where($where)->count();

        return $list;
    }
    /*
         * 得到订单成功数量和销量
        */
    public function getSuccessTotal($where = []) {
        $list = Db::name('TransportOrder')->alias('a')->where($where)->field(' count(id) order_amount,sum(final_price) tran_total')->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }
}
