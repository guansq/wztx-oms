<?php

namespace app\admin\logic;

use \think\Db;

class Share extends BaseLogic
{

    /*
       * 得到分享分页id
       */
    public function getListInfo($start, $length, $where = [])
    {
        $list = Db::name('ShareList')->field('shareid,type')->group('shareid and type')->limit("$start,$length")->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
    /*
       * 得到分享分页列表
       */
    public function getListItem($where=[])
    {

        $list = Db::name('ShareList')->where($where)->field('count(*) num ,SUM(amount) total,sharename,shareid')->select();
       // echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = [])
    {
        $list = Db::name('ShareList')->field('shareid,type')->group('shareid and type')->where($where)->count();
        return $list;
    }
}
