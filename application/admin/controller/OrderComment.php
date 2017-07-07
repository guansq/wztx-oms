<?php
namespace app\admin\controller;
use think\Request;
class OrderComment extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
//        $where =[];
//        $start = input('start') == '' ? 0 : input('start');
//        $length = input('length') == '' ? 10 : input('length');
//        $orderCommentLogic = Model('OrderComment', 'logic');
//        $list = $orderCommentLogic->getListInfo($start, $length, $where);
        //$name = $orderCommentLogic->getSpName('3');
        //var_dump($name);
        return view();
    }


    /**
     * 得到评论列表
     */

    public function getCommentList()
    {
        $where = [];
        $get = input('param.');
        // 应用搜索条件
        foreach (['ordernum', 'name'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'name') {
                    $where['sp_name|dr_name'] = ['like', "%{$get[$key]}%"];
                } elseif ($key == 'ordernum') {
                    $where['pay_orderid'] = ['like', "%{$get[$key]}%"];
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        //评论时间
        if (!empty($get['begintime']) && !empty($get['endtime'])) {
            $where['post_time'] = array('between', array(strtotime($get['begintime']), strtotime($get['endtime'])));
        } elseif (!empty($get['begintime'])) {
            $where['post_time'] = array('egt', strtotime($get['begintime']));
        } elseif (!empty($get['endtime'])) {
            $where['post_time'] = array('elt', strtotime($get['endtime']));
        }

        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $orderCommentLogic = Model('OrderComment', 'logic');
        $list = $orderCommentLogic->getListInfo($start, $length, $where);

        $returnArr = [];
        foreach ($list as $k => $v) {
            //	订单编号	评论者	发货时效	服务态度	满意度	评论时间	评论信息
            $action = '';
            if (!empty($v['status'])) {
                $action = '<button type="button" class="btn btn-primary changestatus" onclick="changeStauts(\'0\',\''.$v['id'].'\',\'确定不屏蔽了？\');" data-msg="确定不屏蔽了？" data-status="0" id="'.$v['id'].'">重新显示</button>';
            } else {
                $action = '<button type="button" class="btn btn-primary changestatus" onclick="changeStauts(\'1\',\''.$v['id'].'\',\'确定屏蔽了？\');" data-msg="确定屏蔽了？"  data-status="1" id="'.$v['id'].'">屏蔽</button>';
            }
            $returnArr[] = [
                'id' => $v['id'],//id
                'pay_orderid' => $v['pay_orderid'],//订单编号
                'spbasename' => $v['sp_name'],//评论者
                'limitship' => $v['limit_ship'] . '星',//发货时效几星
                'attitude' => $v['attitude'] . '星',//服务态度几星
                'satisfaction' => $v['satisfaction'] . '星',//满意度 几星
                'posttime' => date('Y-m-d H:i:s', $v['post_time']),//评论时间
                'content' => $v['content'],//评论信息
                'status' => $v['status'], //是否屏蔽
                'action' => $action
            ];
        }
        $total = $orderCommentLogic->getListNum($where);
        //var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }
    //更新屏蔽状态
    public function updateStatus()
    {
        $id = input('id');
        $statu = input('status');
        $orderCommentLogic = model('OrderComment', 'logic');
        $status = ['status' =>$statu, 'update_at' => time()];
        $detail = $orderCommentLogic->updateStatus(['id' => $id],$status);
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request $request
     * @param  int $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
