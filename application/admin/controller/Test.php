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
use service\HttpService;
use think\Request;

class Test extends BaseController{
    public $url="http://oss.ruitukeji.com/index/uploadFiles";
    function index(){
//        $sendsmsLogic = Model('SendSms', 'logic');
//        $list = $sendsmsLogic->sendText('18094330821','验证成功');
//        var_dump($list);
        return view();
    }

    function add(){
        //\think\File $file
      //  $info = $file->move("/upload","test.jpg");
       /* $data = [
            'rt_appkey'=>'wztx',
            'file'=>'@'.$info->getFilename(),
        ];
        $return_data = HttpService::post($this->url,$data);
        var_dump($return_data);
        if (empty($return_data)){
            //      return resultArray();

        }*/
        // 获取表单上传文件 image可以改名，但要保证一致。
        $file = request()->file('image');

        if(empty($file)){
            returnJson(4001);
        }
        $rule = ['size' => 1024*1024*5, 'ext' => 'jpg,gif,png,jpeg'];
        //validateFile($file, $rule);
        $logic = model('File', 'logic');
        returnJson($logic->uploadImg($file));
        die();
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads','test.jpg');
     //   var_dump($info);
        //var_dump($file->getPathname());
      //  die();
        $data = [
            'rt_appkey'=>'wztx',
            'file'=>'@'.$info->getPathname(),
        ];
       $return_data = HttpService::post($this->url,$data);
       var_dump($return_data);
       if (empty($return_data)){
           //      return resultArray();

       }
        // 获取表单上传文件 例如上传了001.jpg
    //    $file =$this->request->file('image');
        var_dump( $file);
     //   $file = $this->request->file('file');
      // if (($info = $this->request->file('file')->move('static' . DS . 'upload' . DS . $md5s[0], $md5s[1], true))) {

      //      var_dump($file);
        // 移动到框架应用根目录/public/uploads/ 目录下
//        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
//        if($info){
//// 成功上传后 获取上传信息
//// 输出 jpg
//            echo $info->getExtension();
//// 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getSaveName();
//// 输出 42a79759f284b767dfcb2a0197904287.jpg
//            echo $info->getFilename();
//        }else{
//// 上传失败获取错误信息
//            echo $file->getError();
//        }

//        var_dump($file);
//        $data = [
//            'rt_appkey'=>'wztx',
//            'file'=>'@'.$file->getFilename(),
//        ];
//        $return_data = HttpService::post($this->url,$data);
//        var_dump($return_data);
    }
    function edit(){

    }
    /**
     * 删除文章
     */


}