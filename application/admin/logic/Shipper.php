<?php

namespace app\admin\logic;

use \think\Db;

class Shipper extends BaseLogic
{

    /*
       * 得到货主端分页列表
       */
    public function getListInfo($start, $length, $where = [])
    {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpPersonAuth b', 'a.id=b.spbaseid', 'left')
                ->where($where)->limit("$start,$length")->field('a.id,a.auth_state,a.bond_state,a.phone ,b.name,b.sex,a.bond,a.is_black')->select();
            //echo $this->getLastSql();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.spbaseid', 'left')
                ->join('SpOperateInfo c', 'b.id=c.spauthid', 'left')->where($where)->limit("$start,$length")
                ->field('a.id,b.companynickname,b.phone companyphone,b.province,c.number,a.auth_state,a.bond_state,a.bond,a.is_black')->select();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        //  dump($list);die;
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = [])
    {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpPersonAuth b', 'a.id=b.spbaseid', 'left')
                ->where($where)->field('a.id,a.auth_state,a.bond_state,a.phone ,b.name,b.sex,a.is_black')->count();;
            //echo $this->getLastSql();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.spbaseid', 'left')
                ->join('SpOperateInfo c', 'b.id=c.spauthid', 'left')->where($where)
                ->field('a.id,b.companynickname,b.phone companyphone,b.province,c.number,a.auth_state,a.bond_state,a.is_black')->count();
        }
        return $list;
    }

    /*
       * 得到货主端认证信息
       */
    public function getShipperdetail($where = [])
    {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpPersonAuth b', 'a.id=b.spbaseid', 'left')
                ->where($where)->field('a.id,a.auth_state,a.bond_state,a.phone ,b.*,a.bond,a.is_black,a.auth_info,a.frozen_info')->select();
            //echo $this->getLastSql();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.spbaseid', 'left')
                ->join('SpOperateInfo c', 'b.id=c.spauthid', 'left')->where($where)
                ->field('a.id,b.*,c.number personnumber,c.backpic personbackpic,c.frontpic personfrontpic,c.holdpic,a.auth_state,a.bond_state,a.bond,a.is_black,a.auth_info,a.frozen_info')->select();
            // echo $this->getLastSql();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改货主端认证状态
    function updateStatus($where, $status)
    {
        $list = Db::name('SpBaseInfo')->where($where)->update($status);
        return $list;
    }

    //修改货主端黑名单状态
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
