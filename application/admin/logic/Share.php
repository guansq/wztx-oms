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
        $list = Db::name('ShareList')->field('share_id,type')->group('share_id and type')->limit("$start,$length")->order('create_at desc')->where($where)->select();
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

        $list = Db::name('ShareList')->where($where)->field('count(*) num ,SUM(amount) total,share_name,share_id,code')->select();
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
        $list = Db::name('ShareList')->field('share_id,type')->group('share_id and type')->where($where)->count();
        return $list;
    }
}
