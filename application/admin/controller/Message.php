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

class Message extends BaseController {
    protected $table = 'SystemMessage';
    protected $title = '系统消息管理';

    function index() {
        $this->assign('title', $this->title);
        return view();
    }

    public function getMessageList() {
        $where = [];
        $get = input('param.');
        // 应用搜索条件
        foreach (['title', 'type'] as $key) {
            //$get[$key] = trim($get[$key]);
            if (isset($get[$key]) && $get[$key] !== '' && $get[$key] != 'all') {
                $where[$key] = $get[$key];
            }
        }
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $list = Db::name($this->table)->field('*')->where($where)->limit($start, $length)->select();
        //  var_dump($list);
        $returnArr = [];
        foreach ($list as $k => $v) {
            //$logisticstypes = [0 => '同城/长途物流', 1 => '同城物流', 2 => '长途物流'];
          if($v['type'] == 'all'){
              $typename='货主/司机端';
          }elseif ($v['type'] == 1){
              $typename='司机端';
          }else{
              $typename='货主端';
          }
            $returnArr[] = [
                'id' => $v['id'],//id
                'check' => '<input class="list-check-box" value="'.$v['id'].'" type="checkbox"/>',//id
                'title' => $v['title'],//
                'typename' => $typename,//
                'content' => $v['content'],//
                'action' => '<a class="look"  href="javascript:void(0);" data-open="' . url('Message/addueditor', ['id' => $v['id']]) . '" >查看</a> | 
<a class="look"  data-update="' . $v['id'] . '"  href="javascript:void(0);"  data-field="delete"  data-action="' . url('Message/del', ['id' => $v['id']]) . '" >删除</a>'];
        }
        $total = Db::name($this->table)->field('*')->where($where)->count();
        // var_dump($returnArr);
        $info = ['draw' => time(), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $returnArr, 'extdata' => $where];

        return json($info);
    }

    function add() {
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/#' . $_SERVER["REQUEST_URI"] ;
        $url =  dirname($url).'?'.$_SERVER['QUERY_STRING'];
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

    function addueditor() {
        //  var_dump(input());
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/#' . $_SERVER["REQUEST_URI"] ;
        $url =  dirname($url).'?'.$_SERVER['QUERY_STRING'];
        $articledetail = '';

        if (request()->isPost()) {
            $data = input('param.');
            $data['content'] = $data['editor'];
            if(empty($data['content'])){
                $this->error('内容不能为空');
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            if($result !== false ){
                LogService::write('消息管理', '上传消息成功');
                $this->success('恭喜，保存成功哦！', $url);
            }else{
                LogService::write('消息管理', '上传消息失败');
                $this->error('保存失败，请稍候再试！');
            }
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

    function edit() {

    }

    /**
     * 删除系统消息
     */
    public function del() {
        if (DataService::update($this->table)) {
            LogService::write('文章删除', '文章删除成功'. input("post.id", ''));
            $this->success("文章删除成功！", '');
        }
        LogService::write('文章删除', '文章删除失败'. input("post.id", ''));
        $this->error("文章删除失败，请稍候再试！");
    }

}