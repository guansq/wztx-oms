<?php

namespace app\admin\controller;

use service\LogService;
use think\Request;

class Shipper extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (!empty(input('type')) && in_array(input('type'), ['person', 'company'])) {
            $tpl = input('type');
        } else {
            $tpl = 'person';
        }
        $provincelist = ['北京', '天津', '河北', '山西', '内蒙古', '辽宁', '吉林', '黑龙江',
            '上海', '江苏', '浙江', '安徽', '福建', '江西', '山东', '河南', '湖北', '湖南',
            '广东', '广西', '海南', '重庆', '四川', '贵州', '云南', '西藏', '陕西', '甘肃',
            '青海', '宁夏', '新疆', '台湾', '香港', '澳门', '钓鱼岛'];
        $this->assign('provincelist', $provincelist);
        return view($tpl);
    }

    /**
     * 得到个人和公司货主列表
     */

    public function getSpList()
    {
        $where = [];
        if (empty(input('type')) || !in_array(input('type'), ['person', 'company'])) {
            $info = ['draw' => time(), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'extdata' => $where];
            return json($info);
        }
        $get = input('param.');
        if (input('type') == 'person') {
            $type = 0;
            //用户名称	手机号	性别	保证金状态	认证状态	操作
            // 应用搜索条件
            foreach (['name', 'auth_state', 'bond_state', 'sex'] as $key) {
                if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                    if ($key == 'name') {
                        $where[$key] = ['like', "%{$get[$key]}%"];
                    } else {
                        $where[$key] = $get[$key];
                    }
                }
            }
        } else if (input('type') == 'company') {
            $type = 1;
            // 应用搜索条件
            foreach (['companyname', 'auth_state', 'bond_state', 'province'] as $key) {
                if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                    if ($key == 'province') {
                        $where[$key] = ['like', "%{$get[$key]}%"];
                    } elseif ($key == 'companyname') {
                        $where['b.companynickname'] = $get[$key];
                    } else {
                        $where[$key] = $get[$key];
                    }
                }
            }
        }

        $where['type'] = $type;
        // 应用搜索条件
        /* foreach (['main_name', 'name', 'code', 'pur_attr'] as $key) {
             if (isset($get[$key]) && $get[$key] !== '') {
                 $where[$key] = ['like',"%{$get[$key]}%"];
             }
         }*/
        //var_dump($where);
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $shipperLogic = Model('Shipper', 'logic');
        $list = $shipperLogic->getListInfo($start, $length, $where);

        $returnArr = [];
        if (input('type') == 'person') {
            foreach ($list as $k => $v) {
                //用户名称	手机号	性别	保证金状态	认证状态	操作
                $auth_states = ['init' => '未认证',
                    'pass' => '认证通过',
                    'refuse' => '认证失败',
                    'delete' => '后台删除'];
                $bond_states = ['init' => '未缴纳',
                    'checked' => '已缴纳',
                    'frozen' => '账户冻结',
                    'black' => '黑名单'];
                $sexname = '';
                if ($v['sex'] === 0) {
                    $sexname = '男';
                } elseif ($v['sex'] === 1) {
                    $sexname = '女';
                }
                //  $action = '';
                $returnArr[] = [
                    'id' => $v['id'],//id
                    'name' => $v['name'],//用户名称
                    'phone' => $v['phone'],//手机号
                    'sexname' => $sexname,//性别
                    'auth_state' => $auth_states[$v['auth_state']],//认证状态
                    'bond_state' => $bond_states[$v['bond_state']],//保证金状态
                    'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Shipper/showdetail', ['type' => 'person', 'id' => $v['id']]) . '" >查看</a>',
                ];
            }
        } else if (input('type') == 'company') {
            foreach ($list as $k => $v) {
                //用户名称	手机号	性别	保证金状态	认证状态	操作
                $auth_states = ['init' => '未认证',
                    'pass' => '认证通过',
                    'refuse' => '认证失败',
                    'delete' => '后台删除'];
                $bond_states = ['init' => '未缴纳',
                    'checked' => '已缴纳',
                    'frozen' => '账户冻结',
                    'black' => '黑名单'];
                //  $action = '';
                $returnArr[] = [
                    'id' => $v['id'],//id
                    'name' => $v['companynickname'],//企业名称
                    'phone' => $v['companyphone'],//企业电话
                    'province' => $v['province'],//省
                    'number' => $v['number'],//操作人身份证
                    'auth_state' => $auth_states[$v['auth_state']],//认证状态
                    'bond_state' => $bond_states[$v['bond_state']],//保证金状态
                    'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Shipper/showdetail', ['type' => 'company', 'id' => $v['id']]) . '" >查看</a>',
                ];
            }
        }
        $total = $shipperLogic->getListNum($where);
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
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
        return view('edit');
    }

    /**
     *显示详情
     *
     * @param  int $id
     * @return \think\Response
     */
    public function showdetail($id)
    {
        $where = [];
        $type = 0;
        $id = intval(input('id'));
        if (!empty(input('type')) && in_array(input('type'), ['person', 'company'])) {
            if (input('type') == 'company') {
                $tpl = 'companydetail';
                $type = 1;
            } else {
                $tpl = 'persondetail';
            }
        } else {
            $this->error('未查询到当前用户信息', '');
//            return view('edit');
        }
        $where['type'] = $type;
        $where['a.id'] = $id;
        $shipperLogic = Model('Shipper', 'logic');
        $item = $shipperLogic->getShipperdetail($where);
        if (empty($item)) {
            $this->error('未查询到当前用户信息', '');
        }
        $this->assign('id', $id);
        $this->assign('item', $item[0]);

        return view($tpl);
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

    //审核通过
    public function pass()
    {
        $id = input('id');
        $shipperLogic = model('Shipper', 'logic');
        $status = ['auth_state' => 'pass', 'update_at' => time()];
        $detail = $shipperLogic->updateStatus(['id' => $id], $status);
        // session('user', $user);
        LogService::write('货主端:' . $id, '审核通过');
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        //
    }

    public function auth()
    {
        $titile = input('title');
        $id = input('id');
        $authtype = input('type');
        $shipperLogic = model('Shipper', 'logic');
        $status = ['update_at' => time()];
        $where = ['id' => $id];
        $tmp = '';
        $isblack = 0; //1加入黑名单2移除黑名单
        if (empty($titile)) {
            return json(['code' => 4000, 'msg' => '输入文本不能为空', 'data' => []]);
        }
        switch ($authtype) {
            case 'refuse': //拒绝审核
                $status['auth_state'] = 'refuse';
                $tmp = $titile . ',' . time();
                $where['auth_state'] = 'init';
                $status['auth_info'] = ['exp', 'concat(IFNULL(auth_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'frozen': //冻结账户
                $where['bond_state'] = 'checked';
                $status['bond_state'] = 'frozen';
                $tmp = $titile . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'unfrozen': //取消冻结
                $where['bond_state'] = 'frozen';
                $status['bond_state'] = 'checked';
                $tmp = $titile . ',' . time();
                $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\'' . '-' . $tmp . '\')'];
                break;
            case 'black': //加入黑名单
                $where['is_black'] = '0';
                $status['is_black'] = '1';
                $tmp = $titile . ',' . time();
                $isblack = 1;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            case 'unblack': //从黑名单删除
                $where['is_black'] = '1';
                $status['is_black'] = '0';
                $tmp = $titile . ',' . time();
                $isblack = 2;
                // $status['frozen_info'] = ['exp', 'concat(IFNULL(frozen_info,\'\'),\''.'-'.$tmp.'\')'];
                break;
            default:
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
        if (in_array($isblack, [1, 2])) {
            $blackinfo = ['baseid' => $id, 'phone' => input('phone'), 'reason' => ['exp', 'concat(IFNULL(reason,\'\'),\'' . '-' . $tmp . '\')'], 'type' => '0',];
            $detail = $shipperLogic->updateBlackStatus($isblack, $blackinfo);
            //修改黑名单记录表
            if (empty($detail)) {
                return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
            }
        }
        LogService::write('货主端:' . $id, $authtype . ',' . $titile . ',' . time());
        // 'contract' => ['exp', 'concat(IFNULL(contract,\'\'),\''.','.$src.'\')']
        $detail = $shipperLogic->updateStatus($where, $status);
        if ($detail) {
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        } else {
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }
}
