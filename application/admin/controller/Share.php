<?php

namespace app\admin\controller;

use think\Request;
use service\LogService;
class Share extends BaseController {
    /**
     * 分享设置
     *
     * @return \think\Response
     */
    public function setting() {
        return view();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
        return view();
    }

    /**
     * 得到分享列表-司机和货主端
     */

    public function getShareList() {
        $where = [];
        $get = input('param.');
        foreach (['name','type'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['share_name'] = ['like', "%{$get[$key]}%"];
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $shareLogic = Model('Share', 'logic');
        $listAll = $shareLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        $num = 0;
        foreach ($listAll as $key =>$item){
            $list = $shareLogic->getListItem(['share_id'=>$item['share_id'],'type'=>$item['type']]);
            $returnArr[] = [
                'check' => ++$num,//id
                'accountid' =>  $list[0]['share_id'],//id
                'code' =>  $list[0]['code'],//id
                'sharename' => $list[0]['share_name'],//用户名称
                'typename' =>empty($list[0]['type'])?'货主端':'司机端' ,//
                'num' => $list[0]['num'],//手机号
                'total' =>  $list[0]['total'],
                'action' => ''
                ];
        }

        $total = $shareLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create() {
        return view();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request) {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id) {
        return view('setting');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id) {

    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id) {
        //
    }
}
