<?php

namespace app\admin\logic;

use \think\Db;

class BlackInfo extends BaseLogic {
    /*
     * 得到黑名单分页列表
     */
    public function getListInfo($start, $length, $where = []) {
        $list = Db::name('BlackList')->where($where)->limit("$start,$length")
            ->field('*')->order('create_at desc')->select();

        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }



    //获得筛选总条数
    public function getListNum($where = []) {
        $list = Db::name('BlackList')->where($where)
            ->field('*')->count();
        return $list;
    }


}
