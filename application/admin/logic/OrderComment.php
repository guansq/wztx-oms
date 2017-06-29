<?php

namespace app\admin\logic;

use \think\Db;

class OrderComment extends BaseLogic
{

    /*
       * 得到司机端分页列表
       */
    public function getListInfo($start, $length, $where = [])
    {
        $list = Db::name('DrBaseInfo')->alias('a')->join('DrPersonAuth b', 'a.id=b.drbaseid', 'left')
            ->join('DrCarinfoAuth c', 'b.id=c.drpersonauthid', 'left')->where($where)->limit("$start,$length")
            ->field('a.id,a.phone,b.sex,b.number,b.address,b.name,b.logisticstype,c.cardnumber')->select();

        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }
    /*
      * 得到车型列表
      */
    public function getCarList()
    {
        $list = Db::name('CarStyle')->field('*')->select();
        //echo $this->getLastSql();
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }


    //获得筛选总条数
    public function getListNum($where = [])
    {
        $list = Db::name('DrBaseInfo')->alias('a')->join('DrPersonAuth b', 'a.id=b.drbaseid', 'left')
            ->join('DrCarinfoAuth c', 'b.id=c.drpersonauthid', 'left')->where($where)
            ->field('a.id,a.phone,b.sex,b.number,b.address,b.name,b.logisticstype,c.cardnumber')->count();
        return $list;
    }

    /*
       * 得到司机端基本信息
       */
    public function getDriverInfo($id)
    {
        $list = Db::name('DrBaseInfo')->where(['id'=>$id])->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    /*
       * 得到司机端基本信息
       */
    public function getCarinfoAuth($id)
    {
        $list = Db::name('DrCarinfoAuth')->where(['drpersonauthid'=>$id])->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }
    /*
           * 得到司机端基本信息
           */
    public function getPersonAuth($id)
    {
        $list = Db::name('DrPersonAuth')->where(['drbaseid'=>$id])->field('*')->select();
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }
    //修改司机端认证状态
    function updateStatus($where, $status)
    {
        $list = Db::name('DrBaseInfo')->where($where)->update($status);
        return $list;
    }

    //修改司机端黑名单状态
    function updateBlackStatus($isblack, $blackinfo)
    {
        $where = ['type' => $blackinfo['type'], 'baseid' => $blackinfo['baseid']];
        $list = Db::name('BlackPhone')->where($where)->select();
        if ($isblack == 1) {
            $blackinfo['is_del'] = 0;
            if (empty($list)) {
                $blackinfo['create_at'] = time();
                $result = Db::name('BlackPhone')->insert($blackinfo);
            } else {
                $blackinfo['update_at'] = time();
                $result = Db::name('BlackPhone')->where($where)->update($blackinfo);
            }
        } elseif ($isblack == 2) {
            $blackinfo['is_del'] = 1;
            if (empty($list)) {
                $blackinfo['create_at'] = time();
                $result = Db::name('BlackPhone')->insert($blackinfo);
            } else {
                $blackinfo['update_at'] = time();
                $result = Db::name('BlackPhone')->where($where)->update($blackinfo);
            }
        }
        return $result;
    }
}
