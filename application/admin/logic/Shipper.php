<?php

namespace app\admin\logic;

use \think\Db;
use service\DataService;
class Shipper extends BaseLogic {
    /*
     * 得到货主端分页列表
     */
    public function getListInfo($start, $length, $where = []) {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')
                ->where($where)->limit("$start,$length")->field('*')->order('create_at desc')->select();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)->limit("$start,$length")
                ->field('a.*,b.com_name,b.phone companyphone,b.address,a.phone moblie')->order('a.create_at desc')->select();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //获得筛选总条数
    public function getListNum($where = []) {
        if (empty($where) || empty($where['type'])) {
            $list = Db::name('SpBaseInfo')
                ->where($where)->field('*')->count();
        } else {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)
                ->field('a.*,b.com_name,b.phone companyphone,b.address')->count();
        }
        return $list;
    }

    /*
     * 得到货主端认证信息
     */
    public function getShipperdetail($where = []) {
        if ($where['type'] == 'person') {
            $list = Db::name('SpBaseInfo')->alias('a')
                ->where($where)->field('*')->select();
            //  echo $this->getLastSql();
        } else if ($where['type'] == 'company') {
            $list = Db::name('SpBaseInfo')->alias('a')->join('SpCompanyAuth b', 'a.id=b.sp_id', 'left')
                ->where($where)
                ->field('a.*,a.back_pic person_back_pic,a.hold_pic person_hold_pic,a.front_pic person_front_pic,b.*,b.back_pic com_back_pic,b.front_pic com_front_pic,b.identity com_identity,a.identity person_identity,b.hold_pic com_hold_pic')->select();
          // echo $this->getLastSql();
        }
        if ($list) {
            $list = collection($list)->toArray();
        }
        return $list;
    }

    //修改货主端认证状态
    public function updateStatus($where, $status) {
        $list = Db::name('SpBaseInfo')->where($where)->update($status);
        return $list;
    }
    //删除黑名单
    public function delBlack($blackinfo=[]){
        if(empty($blackinfo)){
            return false;
        }
        $spdetail = Db::name('SpBaseInfo')->where([ 'id' => $blackinfo['user_id']])->find();
        if(empty($spdetail)){
            return false;
        }

        if($spdetail['type'] == 'person'){
            $data['update_at'] = time();
            $data['type'] = 2;
            $data['name'] = $spdetail['real_name'];
            $data['number'] = $spdetail['identity'];
            $data['user_id'] = $blackinfo['user_id'];
            $data['reason'] = $blackinfo['reason'];

            $isexist = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->find();
            if(!empty($isexist)){
                $result = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->update(['is_del'=>1,'update_at'=>time()]);
            }
            $dataphone['type'] = 0;
            $dataphone['phone'] = $spdetail['phone'];
            $dataphone['reason'] = $blackinfo['reason'];
            $dataphone['user_id'] = $blackinfo['user_id'];

            $isexist = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->find();
            if(!empty($isexist)){
                $result = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->update(['is_del'=>1,'update_at'=>time()]);
            }
            return true;
        }else if($spdetail['type'] == 'company'){
            $dataphone['type'] = 0;
            $dataphone['phone'] = $spdetail['phone'];
            $dataphone['reason'] = $blackinfo['reason'];
            $dataphone['user_id'] = $blackinfo['user_id'];

            $isexist = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->find();
            if(!empty($isexist)){
                $result = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->update(['is_del'=>1,'update_at'=>time()]);
            }
            $data['update_at'] = time();
            $data['type'] = 3;
            $companydetail =  Db::name('SpCompanyAuth')->where(['id'=>$spdetail['company_id']])->find();
            if(!empty($companydetail)){
                $data['name'] = $companydetail['com_name'];
                $data['number'] = $companydetail['com_buss_num'];
                $data['user_id'] = $blackinfo['user_id'];
                $data['reason'] = $blackinfo['reason'];

                $isexist = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->find();
                if(!empty($isexist)){
                    $result = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->update(['is_del'=>1,'update_at'=>time()]);
                }
            }
            return true;
        }
        return true;
    }
    //添加黑名单
    public function addBlack($blackinfo=[]){
        if(empty($blackinfo)){
            return false;
        }
        $spdetail = Db::name('SpBaseInfo')->where([ 'id' => $blackinfo['user_id']])->find();
        if(empty($spdetail)){
            return false;
        }

        if($spdetail['type'] == 'person'){
            $data['update_at'] = time();
            $data['type'] = 2;
            $data['name'] = $spdetail['real_name'];
            $data['number'] = $spdetail['identity'];
            $data['user_id'] = $blackinfo['user_id'];
            $data['reason'] = $blackinfo['reason'];

            $isexist = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->find();
            if(empty($isexist)){
                $data['create_at'] = time();
                $result = DataService::save('BlackList', $data);
            }else{
                $result = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->update(['is_del'=>0,'update_at'=>time()]);
            }
            $dataphone['type'] = 0;
            $dataphone['phone'] = $spdetail['phone'];
            $dataphone['reason'] = $blackinfo['reason'];
            $dataphone['user_id'] = $blackinfo['user_id'];

            $isexist = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->find();
            if(empty($isexist)){
                $dataphone['create_at'] = time();
                $result = DataService::save('BlackList', $dataphone);
            }else{
                $result = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->update(['is_del'=>0,'update_at'=>time()]);
            }
            return true;
        }else if($spdetail['type'] == 'company'){
            $dataphone['type'] = 0;
            $dataphone['phone'] = $spdetail['phone'];
            $dataphone['reason'] = $blackinfo['reason'];
            $dataphone['user_id'] = $blackinfo['user_id'];

            $isexist = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->find();
            if(empty($isexist)){
                $dataphone['create_at'] = time();
                $result = DataService::save('BlackList', $dataphone);
            }else{
                $result = Db::name('BlackList')->where(['phone'=>$dataphone['phone'],'type'=>$dataphone['type']])->update(['is_del'=>0,'update_at'=>time()]);
            }
            $data['update_at'] = time();
            $data['type'] = 3;
            $companydetail =  Db::name('SpCompanyAuth')->where(['id'=>$spdetail['company_id']])->find();
            if(!empty($companydetail)){
                $data['name'] = $companydetail['com_name'];
                $data['number'] = $companydetail['com_buss_num'];
                $data['user_id'] = $blackinfo['user_id'];
                $data['reason'] = $blackinfo['reason'];

                $isexist = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->find();
                if(empty($isexist)){
                    $data['create_at'] = time();
                    $result = DataService::save('BlackList', $data);
                }else{
                    $result = Db::name('BlackList')->where(['name'=>$data['name'],'number'=>$data['number'],'type'=>$data['type']])->update(['is_del'=>0,'update_at'=>time()]);
                }
            }
            return true;
        }
        return true;
    }
    //修改货主端黑名单状态
    public function updateBlackStatus($isblack, $blackinfo) {
        if ($isblack == 1) {
            return $this->addBlack($blackinfo);
        } elseif ($isblack == 2) {
            return $this->delBlack($blackinfo);
        }
    }

    //通过sp_id获得系统表中id
    public function getSystemShipperIds($where = []) {
        $list = Db::name('SpBaseInfo')->where($where)->field('user_id')->select();
        return $list;
    }
    //通过sp_id删除基本信息表
    public function delSpBaseInfoIds($where = []) {
        $list = Db::name('SpBaseInfo')->where($where)->delete();
        return $list;
    }
    //删除系统表中货主信息
    public function delSystemShipperIds($where = []) {
        $list = Db::name('SystemUserShipper')->where($where)->delete();
        return $list;
    }

    /*
     * 得到某种状态的数量
    */
    public function getListTotalNum($where = []) {
        $list = Db::name('SpBaseInfo')->alias('a')->where($where)->count();
        //echo $this->getLastSql();
        return $list;
    }
}
