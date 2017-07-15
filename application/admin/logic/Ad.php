<?php

namespace app\admin\logic;

use \think\Db;

class Ad extends BaseLogic
{

    /*
          * 得到广告列表
          */
    public function getListInfo($start, $length, $where = [])
    {
        $list = Db::name('Advertisement')->where($where)->limit("$start,$length")
            ->field('*')->order('create_at desc')->select();
      //  echo $this->getLastSql();

        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
    //获得筛选总条数
    public function getListNum($where = [])
    {
        $list = Db::name('Advertisement')->where($where)
            ->field('*')->count();
        return $list;
    }
    //修改广告状态
    function updateStatus($where, $status)
    {
        $list = Db::name('Advertisement')->where($where)->update($status);
        return $list;
    }
    public function getAdOneInfo( $where = [])
    {
        $list = Db::name('Advertisement')->where($where)->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
}
