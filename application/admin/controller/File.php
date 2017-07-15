<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/13
 * Time: 10:55
 */
namespace app\admin\controller;

class File extends BaseController{
    /**
     * @api      {POST} /file/uploadImg  上传图片done
     * @apiName  uploadImg
     * @apiGroup File
     * @apiHeader {String} authorization-token           token.
     * @apiParam {Image} file    上传的文件 最大5M 支持'jpg', 'gif', 'png', 'jpeg'
     * @apiSuccess {String} url  下载链接(绝对路径)
     */
    public function uploadImg(){
        $file = $this->request->file('file');

        if(empty($file)){
            returnJson(4001);
        }
        $rule = ['size' => 1024*1024*5, 'ext' => 'jpg,gif,png,jpeg'];
        //validateFile($file, $rule);
        $logic = model('File', 'logic');
        returnJson($logic->uploadImg($file,$this->loginUser));
    }
}