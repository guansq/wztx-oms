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

class Article extends BaseController {
    protected $table = 'SystemArticle';
    protected $title = '文章管理';

    function index() {
        $where = [];
        if (!empty(input('name'))) {
            $where['title'] = ['like', "%" . input('name') . "%"];
        }
        $db = Db::name($this->table);
        $list = $db->field('*')->where($where)->select();
        $num = 0;
        foreach ($list as $k =>$v){
            $num =$num+1;
            $list[$k]['content'] = mb_substr( strip_tags( $v['content']),0,20);
            $list[$k]['num'] = $num;
            $list[$k]['name'] = ($v['show_type']==1)?'司机端':'货主端';
        }

        $this->assign('list',$list);
        //$this->assign('title', $this->title);
        return view();
    }

    function add() {
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/#' . $_SERVER["REQUEST_URI"];
        $url = dirname($url) . '?' . $_SERVER['QUERY_STRING'];
        $articledetail = '';

        if (request()->isPost()) {
            $data = input('param.');
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', $url) : $this->error('保存失败，请稍候再试！');
            //return true;
        } else {
            $id = input('id');
            if (!empty($id)) {
                $articledetail = Db::name($this->table)->where('id', $id)->find();
                $articledetail['name']=($articledetail['show_type']==1)?'司机端':'货主端';
            }
            $this->assign('articledetail', $articledetail);
            return view();
        }
    }

    function addueditor() {
        //  var_dump(input());
        $url = str_replace($_SERVER['SERVER_NAME'] . '/', $_SERVER['SERVER_NAME'] . '/#/', Url::build('Article/index')) . '?' . $_SERVER['QUERY_STRING'];
        $url = preg_replace('/s=[^\s]*&/', '', $url);

//        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/#' . $_SERVER["REQUEST_URI"] ;
//        $url =  dirname($url).'?'.$_SERVER['QUERY_STRING'];
        $articledetail = '';
        $this->assign('uploadurl', url('admin/plugs/uploadSource'));
    //    $this->assign('title', $this->title);
        if (request()->isPost()) {
            $data = input('param.');
            $data['content'] = $data['editor'];
            if (empty($data['content'])) {
                $this->error('内容不能为空');
            }
            if (!isset($data['id'])) {
                $num = Db::name($this->table)->where(['type' => $data['type']])->field('*')->count();
                if ($num > 0) {
                    $this->error('文章标识必须唯一');
                }
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', $url) : $this->error('保存失败，请稍候再试！');
            //return true;
        } else {
            $id = input('id');
            if (!empty($id)) {
                $articledetail = Db::name($this->table)->where('id', $id)->find();
                $articledetail['name']=($articledetail['show_type']==1)?'司机端':'货主端';
            }
            $this->assign('articledetail', $articledetail);
            return view();
        }
    }

    function edit() {

    }

    /**
     * 删除文章
     */
    public function del() {

        if (DataService::update($this->table)) {
            LogService::write('文章删除', '文章删除成功' . input("post.id", ''));
            $this->success("文章删除成功！", '');
        }
        LogService::write('文章删除', '文章删除失败' . input("post.id", ''));
        $this->error("文章删除失败，请稍候再试！");
    }

}