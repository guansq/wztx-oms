<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/13
 * Time: 10:55
 */
namespace app\admin\logic;
use service\HttpService;
class SendSms extends BaseLogic {
    private $url = "http://pushmsg.ruitukeji.com/SendSms/sendText";
    //发送文本信息
    public function sendText($moblie,$text){
        $url = "http://pushmsg.ruitukeji.com/SendSms/sendText";
        if(empty($moblie) || empty($text)){
            returnJson('4001','手机号码或者发送文本信息为空');
        }
        $data = ["mobile"=>$moblie,"text"=>$text,"rt_appkey"=>"WZTX"];
        $return_data = HttpService::post($url, $data);
        return $return_data;
    }
}