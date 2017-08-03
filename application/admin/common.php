<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/4
 * Time: 17:08
 */
use service\NodeService;
use service\DataService;
use think\Db;
use DesUtils\DesUtils;
use service\HttpService;
/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */

function auth($node) {
    return NodeService::checkAuthNode($node);
}

/*
 * 发送信息 $basetype =0 为发送信息给货主 $basetype =1 为发送信息给司机
 */
function sendMsg($sendeeId,$title,$content,$basetype=0,$type='single',$pri=3){
    $data = [
        'title' => $title,
        'content' => $content,
        'type' => $basetype,
        'publish_time' => time(),
        'pri' => $pri,
        'push_type'=>$type,
        'create_at' => time(),
        'update_at' => time()
    ];
    $msgId = model('Message')->saveMsg($data);
    $data = [
        'msg_id' => $msgId,
        'sendee_id' => $sendeeId,
        'type' => 0,
        'create_at' => time(),
        'update_at' => time()
    ];
    $res = model('MessageSendee')->saveSendee($data);
    return $res;
}

/*
 * 发送短信 推送给货主为$rt_key='wztx_shipper' 推送给司机为 $rt_key='wztx_driver'
 */
function sendSMS($phone,$content,$rt_key='wztx_shipper'){
    $sendData = [
        'mobile' => $phone,
        'rt_appkey' => 'wztx_shipper',
        "req_time" => time(),
        "req_action" => 'sendText',
        'text' => $content,
    ];
    //进行签名校验
    $sendData['sign'] = createSign($sendData);
    HttpService::post(getenv('APP_API_MSG').'SendSms/sendText',$sendData);//sendSms($data)
}


/*
 * 推送信息 推送给货主为$rt_key='wztx_shipper' 推送给司机为 $rt_key='wztx_driver'
 */
function pushInfo($token,$title,$content,$rt_key='wztx_shipper'){
    $sendData = [
        "platform" => "all",
        "rt_appkey" => $rt_key,
        "req_time" => time(),
        "req_action" => 'push',
        "alert" => $title,
        "regIds" => $token,
        //"platform" => "all",
        "androidNotification" => [
            "alert" => $title,
            "title" => $content,
            "builder_id" => "builder_id",
            "priority" => 0,
            "style" => 0,
            "alert_type" => -1,
            "extras" => [
                "0" => "RuiTu",
                "key" => "value"
            ]
        ]
    ];
    $desClass = new DesUtils();
    $arrOrder = $desClass->naturalOrdering([$sendData['rt_appkey'],$sendData['req_time'],$sendData['req_action']]);
    $skArr = explode('_',config('app_access_key'));
    $sendData['sign'] = $desClass->strEnc($arrOrder,$skArr[0],$skArr[1],$skArr[2]);//签名
    $result = HttpService::post(getenv('APP_API_HOME').'push',http_build_query($sendData));
    //dump($result);
}
/*
 * 得到司机推送token
 */
function getDrPushToken($id){
    return Db::name('system_user_driver')->where("id",$id)->value('push_token');
}
/*
 * 得到货主token
 */
function getSpPushToken($id){
    return Db::name('system_user_shipper')->where("id",$id)->value('push_token');
}

/*
 * 生成签名
 */
function createSign($sendData){
    $desClass = new DesUtils();
    $arrOrder = $desClass->naturalOrdering([$sendData['rt_appkey'],$sendData['req_time'],$sendData['req_action']]);
    $skArr = explode('_',config('app_access_key'));
    return $desClass->strEnc($arrOrder,$skArr[0],$skArr[1],$skArr[2]);//签名
}