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
        $this->assign('articledetail','');
        if(request()->isPost()){
            $data=input('param.');
            $result = $this->validate($data,'Article');
            if($result !== true){
                $this->error($result);
            }
            $result = DataService::save($this->table, $data);//Db::name($this->table)->allowField(true)->insert($data);
            LogService::write('Article管理', '上传Article成功');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            return view();
        }
    }
    function edit(){
        $id = input('id');
        if(empty($id)){
            $this->error('当前文章无法编辑!');
        }
        $articledetail = Db::name($this->table)->where('id', $id)->select();
        $this->assign('articledetail',$articledetail[0]);
        return view('add');
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