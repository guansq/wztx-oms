<?php

namespace app\admin\controller;

use think\Request;
use service\LogService;
use service\DataService;

class Ad extends BaseController {
    protected $table = 'Advertisement';
    protected $title = 'advertisement';

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index() {
        return view();
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
        //
        return view('edit');
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

    public function add() {
        if (request()->isPost()) {
            $data = input('param.');
            //var_dump($data);
            if (empty($data['addbegintime']) || empty($data['addendtime']) || $data['addport'] == 'all' || empty($data['src'] || empty($data['typepost']))) {
                $this->error('保存失败，缺少关键信息');
            }
            $data['begintime'] = strtotime($data['addbegintime']);
            $data['endtime'] = strtotime($data['addendtime']);
            $data['status'] = 0;
            //  $data['src'] = strtotime($data['imgadd']);
            $data['port'] = $data['addport'];
            if ($data['typepost'] != 'add') {
                if (empty($data['idpost'])) {
                    $this->error('缺少id刷新页面，重新选择');
                }
                $data['id'] = $data['idpost'];
            }
            if ($data['typepost'] == 'add') {
                $data['create_at'] = time();
            }
            $data['update_at'] = time();
            $changewhere = [];
            $changewhere['port'] = $data['port'];
            $changewhere['position'] = $data['position'];
            $changestatus['status'] = 1;
            $changestatus['update_at'] = time();
            $adLogic = Model('Ad', 'logic');
            $item = $adLogic->updateStatus($changewhere, $changestatus);
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('广告管理', '上传广告成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }
    }

    /**
     * 得到广告列表
     */

    public function getAdList() {
        $where = [];
        $get = input('param.');
        // 应用搜索条件
        foreach (['begintime', 'endtime', 'port'] as $key) {
            //$get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'begintime') {
                    $where[$key] = array('egt', strtotime($get['begintime']));
                } elseif ($key == 'endtime') {
                    $where[$key] = array('elt', strtotime($get['endtime']));
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $adLogic = Model('Ad', 'logic');
        $list = $adLogic->getListInfo($start, $length, $where);
        $returnArr = [];
        foreach ($list as $k => $v) {
            $portname = '';
            if ($v['port'] == 0) {
                $portname = '货主端';
            } elseif ($v['port'] == 1) {
                $portname = '司机端';
            }
            $statusname =  ($v['status'] == 1) ? '启用' : '禁用'; //显示状态
            //  $action = '';
            $returnArr[] = [
                'id' => $v['id'],//id
                'position' => $v['position'],//序号
                'port' => $portname,//广告显示端口
                'src' => $v['src'],//上传图片
                'url' => $v['url'],//添加链接
                'begintime' => empty($v['begintime']) ? '' : date('Y-m-d', $v['begintime']),//开始时间
                'endtime' => empty($v['endtime']) ? '' : date('Y-m-d', $v['endtime']),//结束时间
                'status' => ($v['status'] == 1) ? '不显示' : '显示', //显示状态
                'action' => '   <button id="edit'.$v['id'].'" type="button" class="btn btn-info mr_20 edit" data-flag="'.$v['id'].'" >修改</button>
                    <button type="button" class="btn btn-info status" value="'.$v['status'].'" id="status'.$v['id'].'" data-flag="'.$v['id'].'" data-port="'.$v['port'].'"data-position="'.$v['position'].'">'.$statusname.'</button>',

                //  'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Driver/showdetail', ['id' => $v['id']]) . '" >查看</a>',
            ];
        }

        $total = $adLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    /**
     * 得到广告单条信息
     */

    public function getAdItem() {
        $get = input('param.');
        $where['id'] = intval(input('id'));
        $adLogic = Model('Ad', 'logic');
        $iteminfo = $adLogic->getAdOneInfo($where);
        $returnArr = [];
        if (!empty($iteminfo)) {
            $returnArr[] = [
                'position' => $iteminfo[0]['position'],//序号
                'port' => $iteminfo[0]['port'],//广告显示端口
                'src' => $iteminfo[0]['src'],//上传图片
                'url' => $iteminfo[0]['url'],//添加链接
                'begintime' => empty($iteminfo[0]['begintime']) ? '' : date('Y-m-d', $iteminfo[0]['begintime']),//开始时间
                'endtime' => empty($iteminfo[0]['endtime']) ? '' : date('Y-m-d', $iteminfo[0]['endtime']),//结束时间
            ];
        }
        return json(['code' => 2000, 'msg' => '成功', 'data' => $returnArr]);
    }

    //修改状态
    public function updateStatus()
    {
        $id = input('id');
        $adLogic = Model('Ad', 'logic');
        $istatus = input('status');
        $istatus  =empty($istatus)?'1':'0';
        if($istatus == 0){
            $changewhere = [];
            $changewhere['port'] = input('port');
            $changewhere['position'] =input('position');
            $changestatus['status'] = 1;
            $changestatus['update_at'] = time();
            $item = $adLogic->updateStatus($changewhere, $changestatus);
        }
        $status = ['status' => $istatus, 'update_at' => time()];
        $detail = $adLogic->updateStatus(['id' => $id], $status);
        LogService::write('广告管理:' . $id, '修改状态'.implode(',',$status).'-'.implode(',',$changewhere));
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }

}
