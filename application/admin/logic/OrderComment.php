<?php

namespace app\admin\logic;

use \think\Db;

class OrderComment extends BaseLogic
{
    /*
       * 得到评论分页列表
       */
    public function getListInfo($start, $length, $where = [])
    {
        $list = Db::name('Comment')->where($where)->limit("$start,$length")
            ->field('*')->order('create_at desc')->select();

        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = [])
    {
        $list = Db::name('Comment')->where($where)->field('*')->count();
        return $list;
    }

    //修改评论显示状态
    function updateStatus($where, $status)
    {
        $list = Db::name('Comment')->where($where)->update($status);
        return $list;
    }
}
