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
class Article extends BaseController{
    protected $table = 'SystemArticle';
    protected $title = '文章管理';

    function index(){

        $db = Db::name($this->table);
        $this->assign('list',   $db->field('*')->select());
        $this->assign('title',$this->title);
        return view();
    }

    function add(){
        $url='http://'.$_SERVER['SERVER_NAME'].'/#'.$_SERVER["REQUEST_URI"].'';
        $url =  dirname($url);
        $articledetail = '';

        if(request()->isPost()){
            $data=input('param.');
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            //LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！',  $url) : $this->error('保存失败，请稍候再试！');
            //return true;
        }else{
            $id = input('id');
            if(!empty($id)){
                $articledetail = Db::name($this->table)->where('id', $id)->select();
                $articledetail = $articledetail[0];
            }
            $this->assign('articledetail',$articledetail);
            return view();
        }
    }
    function addueditor(){
      //  var_dump(input());
        $url='http://'.$_SERVER['SERVER_NAME'].'/#'.$_SERVER["REQUEST_URI"].'';
        $url =  dirname($url);
        $articledetail = '';

        if(request()->isPost()){
            $data=input('param.');
            $data['content'] = $data['editor'];
            if(!isset( $data['id'])){
                $num = Db::name($this->table)->where(['type'=>$data['type']])->field('*')->count();
                if($num > 0 ){
                    $this->error('type值必须唯一');
                }
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            //LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！',  $url) : $this->error('保存失败，请稍候再试！');
            //return true;
        }else{
            $id = input('id');
            if(!empty($id)){
                $articledetail = Db::name($this->table)->where('id', $id)->select();
                $articledetail = $articledetail[0];
            }
            $this->assign('articledetail',$articledetail);
            return view();
        }
    }

    function edit(){

    }
    /**
     * 删除文章
     */
    public function del() {

        if (DataService::update($this->table)) {
            $this->success("文章删除成功！", '');
        }
        $this->error("文章删除失败，请稍候再试！");
    }

}