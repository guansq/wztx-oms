<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/19
 * Time: 14:08
 */

namespace app\admin\controller;

use service\LogService;
use service\DataService;
use think\Db;
use think\Url;

class Message extends BaseController {
    protected $table = 'Message';
    protected $title = '系统消息管理';

    function index() {
        $this->assign('title', $this->title);
        return view();
    }

    public function getMessageList() {
        $where = [];
        $get = input('param.');
        // var_dump($get);
        // 应用搜索条件
        foreach (['title', 'type'] as $key) {
            //$get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                if ($key == 'title') {
                    $where['title'] = ['like', "%{$get[$key]}%"];
                } else {
                    $where[$key] = $get[$key];
                }
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $where['push_type'] = 'all';
        // var_dump($where);
        $list = Db::name($this->table)->field('*')->where($where)->limit($start, $length)->select();
        //  var_dump($list);
        $returnArr = [];
        foreach ($list as $k => $v) {
            //$logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
            if ($v['type'] == '0') {
                $typename = '货主端';
            } elseif ($v['type'] == 1) {
                $typename = '司机端';
            } else {
                $typename = '';
            }
            $returnArr[] = [
                'id' => $v['id'],//id
                'check' => '<input class="list-check-box" value="' . $v['id'] . '" type="checkbox"/>',//id
                'title' => $v['title'],//
                'typename' => $typename,//
                'content' => $v['content'],//
                'is_del' => empty($v['delete_at']) ? '正常' : '已删除',
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Message/addmessage', ['id' => $v['id']]) . '" >查看</a> | 
<a class="look"  data-update="' . $v['id'] . '"  href="javascript:void(0);"  data-field=""  data-action="' . url('Message/del', ['id' => $v['id']]) . '" >删除</a> | 
<a class="look"  data-update="' . $v['id'] . '"  href="javascript:void(0);"  data-field=""  data-action="' . url('Message/show', ['id' => $v['id']]) . '" >展示</a>'];
        }
        $total = Db::name($this->table)->field('*')->where($where)->count();
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    function add() {
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/#' . $_SERVER["REQUEST_URI"];
        $url = dirname($url) . '?' . $_SERVER['QUERY_STRING'];
        $articledetail = '';

        if (request()->isPost()) {
            $data = input('param.');
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            //LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', $url) : $this->error('保存失败，请稍候再试！');
            //return true;
        } else {
            $id = input('id');
            if (!empty($id)) {
                $articledetail = Db::name($this->table)->where('id', $id)->select();
                $articledetail = $articledetail[0];
            }
            $this->assign('articledetail', $articledetail);
            return view();
        }
    }

    function addmessage() {
        $url = str_replace($_SERVER['SERVER_NAME'] . '/', $_SERVER['SERVER_NAME'] . '/#/', Url::build('Message/index')) . '?' . $_SERVER['QUERY_STRING'];
        $url = preg_replace('/s=[^\s]*&/', '', $url);
        $articledetail = '';

        if (request()->isPost()) {
            $data = input('param.');
            $data['content'] = $data['editor'];
            if (empty($data['content'])) {
                $this->error('内容不能为空');
            }
            if ($data['type'] == 'all') {
                $this->error('必须选择客户端类型');
            }
            $data['push_type'] = 'all';
            $data['pri'] = '3';

            if (empty($data['id'])) {
                $data['publish_time'] = time();
                $data['create_at'] = time();
            } else {
                $data['update_at'] = time();
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            if ($result !== false) {
                if (empty($data['id'])) {
                    //推送消息
                    //发送推送消息
                    if ($data['type'] == 0) {
                        $re_key = 'wztx_shipper';
                    } else if ($data['type'] == 1) {
                        $re_key = 'wztx_driver';
                    }
                    if (in_array($re_key, ['wztx_shipper', 'wztx_driver'])) {
                        pushInfo('', $data['title'], $data['content'], 'wztx_shipper');//推送给
                    }
                }
                LogService::write('消息管理', '上传消息成功');
                $this->success('恭喜，保存成功哦！', $url);
            } else {
                LogService::write('消息管理', '上传消息失败');
                $this->error('保存失败，请稍候再试！');
            }
        } else {
            $id = input('id');
            if (!empty($id)) {
                $articledetail = Db::name($this->table)->where('id', $id)->find();
            }
            $this->assign('articledetail', $articledetail);
            return view();
        }
    }

    function addueditor() {
        $url = str_replace($_SERVER['SERVER_NAME'] . '/', $_SERVER['SERVER_NAME'] . '/#/', Url::build('Message/index')) . '?' . $_SERVER['QUERY_STRING'];
        $url = preg_replace('/s=[^\s]*&/', '', $url);
        $articledetail = '';

        if (request()->isPost()) {
            $data = input('param.');
            $data['content'] = $data['editor'];
            if (empty($data['content'])) {
                $this->error('内容不能为空');
            }
            if ($data['type'] == 'all') {
                $this->error('必须选择客户端类型');
            }
            $data['push_type'] = 'all';
            $data['pri'] = '3';

            if (empty($data['id'])) {
                $data['publish_time'] = time();
                $data['create_at'] = time();
            } else {
                $data['update_at'] = time();
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            if ($result !== false) {
                if (empty($data['id'])) {
                    //推送消息
                    //发送推送消息
                    if ($data['type'] == 0) {
                        $re_key = 'wztx_shipper';
                    } else if ($data['type'] == 1) {
                        $re_key = 'wztx_driver';
                    }
                    if (in_array($re_key, ['wztx_shipper', 'wztx_driver'])) {
                        pushInfo('', $data['title'], $data['content'], 'wztx_shipper');//推送给
                    }
                }
                LogService::write('消息管理', '上传消息成功');
                $this->success('恭喜，保存成功哦！', $url);
            } else {
                LogService::write('消息管理', '上传消息失败');
                $this->error('保存失败，请稍候再试！');
            }
        } else {
            $id = input('id');
            if (!empty($id)) {
                $articledetail = Db::name($this->table)->where('id', $id)->find();
            }
            $this->assign('articledetail', $articledetail);
            return view();
        }
    }

    function edit() {

    }

    /**
     * 删除系统消息
     */
    public function del() {
        $ids = explode(',', input("post.id", ''));
        if (Db::name($this->table)->where('id', 'in', $ids)->update(['delete_at' => time(), 'update_at' => time()])) {
            LogService::write('文章删除', '文章删除成功' . input("post.id", ''));
            $this->success("文章删除成功！", '');
        }
        LogService::write('文章删除', '文章删除失败' . input("post.id", ''));
        $this->error("文章删除失败，请稍候再试！");
    }

    /**
     * 删除系统消息
     */
    public function show() {
        $ids = explode(',', input("post.id", ''));
        if (Db::name($this->table)->where('id', 'in', $ids)->update(['delete_at' => null, 'update_at' => time()])) {
            LogService::write('文章显示', '文章显示' . input("post.id", ''));
            $this->success("文章显示成功！", '');
        }
        LogService::write('文章显示', '文章显示失败' . input("post.id", ''));
        $this->error("文章显示失败，请稍候再试！");
    }
}