<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/22
 * Time: 9:45
 */

namespace app\admin\logic;

use service\HttpService;
use CURLFile;
class File extends BaseLogic{

    public $url = 'http://oss.ruitukeji.com/index/uploadFiles';

    // 上传文件 php 5.5
    function uploadFile(\think\File $file){

        //$info = $file->move('/tmp','wztx_tmp_avatar.png');
        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        //$filePath = ROOT_PATH . 'public' . DS . 'upload'. DS .$info->getSaveName();
        $data = [
            'rt_appkey' => 'wztx',
            'file' => '@'.$info->getPathname()
        ];

        $return_data = HttpService::post($this->url, $data);
        if(empty($return_data)){
            return resultArray(4001);
        }
        var_dump($return_data);
        $ossRet = json_decode($return_data,true);
        var_dump($ossRet);
        if(empty($ossRet) || $ossRet['code'] !=2000){
            return resultArray(4001,'',$ossRet);
        }
        return resultArray($ossRet);
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 上传图片
     * @param \think\File $file
     * @param array       $user
     * @return array
     */
    public function uploadImg(\think\File $file, $user = []){
        $fileLogic = model('File', 'logic');
        if(empty($file)){
            return resultArray(4001);
        }
        $ossRet = $fileLogic->uploadFile($file);
        if(empty($ossRet) || $ossRet['code'] != 2000){
            return resultArray($ossRet);
        }
        return resultArray($ossRet);
    }
}