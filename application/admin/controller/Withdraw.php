<?php

namespace app\admin\controller;

use think\Request;

class Withdraw extends BaseController{


    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $where =[];
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $withdrawLogic = Model('Withdraw', 'logic');
        $listAll = $withdrawLogic->getListInfo($start, $length, $where);
        var_dump($listAll);

        $returnArr = [];
        foreach ($listAll as $key =>$item){
            $list1 = $withdrawLogic->getListItem(['id'=>$item['id']]);
            var_dump($list1);
            $returnArr[] = [
//                'accountid' =>  $list[0]['shareid'],//id
//                'sharename' => $list[0]['sharename'],//用户名称
//                'num' => $list[0]['num'],//手机号
//                'total' =>  $list[0]['total'],
//                'action' => ''
            ];
        }


        return view();
    }

    /**
     * 得到提现列表-司机和货主端
     */

    public function getWithDrawList() {
        $where = [];
        $get = input('param.');
        foreach (['name','type'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['sharename'] = ['like', "%{$get[$key]}%"];
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $withdrawLogic = Model('Withdraw', 'logic');
        $list = $withdrawLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        foreach ($list as $key =>$item){
            $list = $withdrawLogic->getListItem(['shareid'=>$item['shareid'],'type'=>$item['type']]);
            $returnArr[] = [
                'accountid' =>  $list[0]['shareid'],//id
                'sharename' => $list[0]['sharename'],//用户名称
                'num' => $list[0]['num'],//手机号
                'total' =>  $list[0]['total'],
                'action' => ''
            ];
        }

        $total = $withdrawLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(){
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request){
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id){
        //
        return view('edit');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id){

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int            $id
     * @return \think\Response
     */
    public function update(Request $request, $id){
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id){
        //
    }
}
