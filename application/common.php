<?php

use service\DataService;
use think\Db;
use service\LogService;
/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = NULL) {
    is_null($pathname) && $pathname = RUNTIME_PATH . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 获取微信操作对象
 * @param string $type
 * @return \Wechat\WechatReceive|\Wechat\WechatUser|\Wechat\WechatPay|\Wechat\WechatScript|\Wechat\WechatOauth|\Wechat\WechatMenu
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $config = [
            'token' => sysconf('wechat_token'),
            'appid' => sysconf('wechat_appid'),
            'appsecret' => sysconf('wechat_appsecret'),
            'encodingaeskey' => sysconf('wechat_encodingaeskey'),
            'mch_id' => sysconf('wechat_mch_id'),
            'partnerkey' => sysconf('wechat_partnerkey'),
            'ssl_cer' => sysconf('wechat_cert_cert'),
            'ssl_key' => sysconf('wechat_cert_key'),
            'cachepath' => CACHE_PATH . 'wxpay' . DS,
        ];
        $wechat[$index] = Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * 安全URL编码
 * @param array|string $data
 * @return string
 */
function encode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(serialize($data)));
}

/**
 * 安全URL解码
 * @param string $string
 * @return string
 */
function decode($string) {
    $data = str_replace(['-', '_'], ['+', '/'], $string);
    $mod4 = strlen($data) % 4;
    !!$mod4 && $data .= substr('====', $mod4);
    return unserialize(base64_decode($data));
}


/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是false为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = false) {
    static $config = [];
    if ($value !== false) {
        $config = [];
        $data = ['name' => $name, 'value' => $value];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        foreach (Db::name('SystemConfig')->select() as $vo) {
            $config[$vo['name']] = $vo['value'];
        }
    }
    return isset($config[$name]) ? $config[$name] : '';
}
/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是false为获取值，否则为更新
 * @return string|bool
 */
function sysconfRemak($name, $value = false) {
    static $config = [];
    if ($value !== false) {
        $config = [];
        $data = ['name' => $name, 'value' => $value];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        foreach (Db::name('SystemConfig')->select() as $vo) {
            $config[$vo['name']] = $vo['remark'];
        }
    }
    return isset($config[$name]) ? $config[$name] : '';
}
/**
 * array_column 函数兼容
 */
if (!function_exists("array_column")) {

    function array_column(array &$rows, $column_key, $index_key = null) {
        $data = [];
        foreach ($rows as $row) {
            if (empty($index_key)) {
                $data[] = $row[$column_key];
            } else {
                $data[$row[$index_key]] = $row[$column_key];
            }
        }
        return $data;
    }

}
// 接口返回json 数据
if (!function_exists('getCodeMsg')) {
    function getCodeMsg($code = 0) {
        $CODE_MSG = [
            0 => '未知错误',
            2000 => 'SUCCESS',
            // 客户端异常
            4000 => '非法请求',
            4001 => '请求缺少参数',
            4002 => '请求参数格式错误',
            4003 => '请求参数格式错误',
            4004 => '请求的数据为空',
            // 客户端异常-用户鉴权
            4010 => '无权访问',
            4011 => 'token丢失',
            4012 => 'token无效',
            4013 => 'token过期',
            // 服务端端异常
            5000 => '服务端异常',
            5010 => '代码异常',
            5020 => '数据库操作异常',
            5030 => '文件操作异常',

            // 调用第三方接口异常
            6000 => '调用第三方接口异常',

        ];

        if (empty($code)) {
            return $CODE_MSG;
        }
        return $CODE_MSG[$code];
    }
}

// 接口返回json 数据
if (!function_exists('returnJson')) {
    function returnJson($code = 0, $msg = '', $data = []) {
        if (empty($data)) {
            $data = new stdClass();
        }
        $info['code'] = $code;
        $info['msg'] = empty($msg) ? getCodeMsg($code) : $msg;
        $info['result'] = $data;
        header('Content-type:application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        exit(json_encode($info));
    }
}

if (!function_exists('assureNotEmpty')) {
    /**
     * Auther: WILL<314112362@qq.com>
     * Time: 2017-3-20 17:51:09
     * Describe: 校验参数是否有空值
     * @return bool
     */
    function assureNotEmpty($params = []) {
        if (empty($params)) {
            returnJson(4001, '缺少必要参数.');
        }
        foreach ($params as $param) {
            if (empty($param)) {
                returnJson(4001, '缺少必要参数或者参数不合法.');
            }
        }
        return true;
    }
}

if (!function_exists("dd")) {

    function dd($obj) {
        var_dump($obj);
        die();
    }

}

/**
 * 生成訂單號
 */
if (!function_exists("generatOrderCode")) {
    function generatOrderCode($prefix = '') {
        /* 选择一个随机的方案 */
        $randStr = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $prefix . $randStr;
    }

}


/**
 * 随机生成 $len 位字符
 */
function randomStr($len = 4) {
    $chars_array = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
        "k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
        "u", "v", "w", "x", "y", "z", "A", "B", "C", "D",
        "E", "F", "G", "H", "I", "J", "K", "L", "M", "N",
        "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X",
        "Y", "Z"
    ];
    $charsLen = count($chars_array) - 1;

    $outputstr = "";
    for ($i = 0; $i < $len; $i++) {
        $outputstr .= $chars_array[mt_rand(0, $charsLen)];
    }
    return $outputstr;
}

// 返回数组
if (!function_exists('resultArray')) {
    function resultArray($result = 0, $msg = '', $data = []) {
        $code = $result;
        if (is_array($result)) {
            $code = $result['code'];
            $msg = $result['msg'];
            $data = $result['result'];
        }
        if (empty($data)) {
            $data = new stdClass();
        }
        $info = [
            'code' => $code,
            'msg' => empty($msg) ? getCodeMsg($code) : $msg,
            'result' => $data
        ];
        return $info;
    }
}

/**
 * 随机生成四位字符
 */
function randomNum($len = 4) {
    $chars_array = [
        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars_array) - 1;

    $outputstr = "";
    for ($i = 0; $i < $len; $i++) {
        $outputstr .= $chars_array[mt_rand(0, $charsLen)];
    }
    return $outputstr;
}

/*
 * PHPexcel读取并返回数组
 */
function format_excel2array11($excelObj, $filePath = '', $sheet = 0) {
    if (empty($filePath) or !file_exists($filePath)) {
        die('file not exists');
    }
    //$PHPReader = new PHPExcel_Reader_Excel2007();        //建立reader对象
    $PHPReader = $excelObj;
    /*dump($PHPReader);
    if(!$PHPReader->canRead($filePath)){
        $PHPReader = new PHPExcel_Reader_Excel5();
        if(!$PHPReader->canRead($filePath)){
            echo 'no Excel';
            return ;
        }
    }*/
    $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
    $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
    $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
    $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
    $data = array();
    for ($rowIndex = 1; $rowIndex <= $allRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        for ($colIndex = 'A'; $colIndex <= $allColumn; $colIndex++) {
            $addr = $colIndex . $rowIndex;
            $cell = $currentSheet->getCell($addr)->getValue();
            if ($cell instanceof PHPExcel_RichText) { //富文本转换字符串
                $cell = $cell->__toString();
            }
            $data[$rowIndex][$colIndex] = $cell;
        }
    }
    return $data;
}

function prDates($start, $end) {
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    while ($dt_start <= $dt_end) {
        echo date('Y-m-d', $dt_start) . "\n";
        $dt_start = strtotime('+1 day', $dt_start);
    }
}


/*
 * 添加推荐列表根据订单情况
 */
function saveOrderShare($order_id = ''){
    if(empty($order_id)){
        return false;
    }
    //获取订单详情
    $orderInfo = model('Order', 'logic')->getListOneInfo([ 'id' => $order_id]);
    if (empty($orderInfo)) {
        return false;
    }
    if(in_array($orderInfo['status'],['pay_success','comment'])){
        $spBaseInfo =getBaseSpUserInfo($orderInfo['sp_id']); // floor($num*100)/100
        $share_money =floor($orderInfo['final_price']*getSysconf('share_percent'))/100;
        if(!empty($spBaseInfo['recomm_id']) && !empty($share_money)){
            $spInviteBaseInfo = getBaseSpUserInfo($spBaseInfo['recomm_id']);
            $whereSp = [
                'share_name'=>$spInviteBaseInfo['real_name'],
                'share_id'=>$spBaseInfo['recomm_id'],
                'status'=>0,
                'type'=>0,
                'code'=>$spInviteBaseInfo['recomm_code'],
                'invite_name'=>$spBaseInfo['real_name'],
                'invite_id'=>$orderInfo['sp_id'],
                'pay_orderid'=>$order_id,
                'amount'=>$share_money,
                'create_at'=>time(),
            ];
            if(saveRecomm($whereSp)){
                $isUpdate =  Db::name('SpBaseInfo')->where(['id'=>$spBaseInfo['recomm_id']])->update(['balance'=>['exp','balance+'.$share_money]]);
            }
        }

        $drBaseInfo = getBaseDrUserInfo($orderInfo['dr_id']);
        if(!empty($drBaseInfo['recomm_id']) && !empty($share_money)){
            $drInviteBaseInfo = getBaseDrUserInfo($drBaseInfo['recomm_id']);
            $whereSp = [
                'share_name'=>$drInviteBaseInfo['real_name'],
                'share_id'=>$drBaseInfo['recomm_id'],
                'status'=>0,
                'type'=>1,
                'code'=>$drInviteBaseInfo['recomm_code'],
                'invite_name'=>$drBaseInfo['real_name'],
                'invite_id'=>$orderInfo['dr_id'],
                'pay_orderid'=>$order_id,
                'amount'=>$share_money,
                'create_at'=>time(),
            ];
            if(saveRecomm($whereSp)){
                $isUpdate =  Db::name('DrBaseInfo')->where(['id'=>$drBaseInfo['recomm_id']])->update(['cash'=>['exp','cash+'.$share_money]]);
            }
        }
        return true;
    }
    return false;
}

//订单结算
function clearOrder($order_id = '') {
    $id = $order_id;
    //状态为支付成功，或者评论的时候更新结算状态,计算结算金额,更新base表里面总的金额   floor($num*100)/100
    $list = getListOneInfo( ['id' => $id]);
    if (empty($list)) {
        return;
    }
    $final_price = $list['final_price'];
    $clear_price =floor($final_price*(100-sysconf('clear_percent')))/100;
    $status = ['is_clear' => '1', 'update_at' => time(),'clear_price'=>$clear_price];
    $where = ['id' => $id,'status'=>['exp','in ("pay_success","comment")'],'is_clear' => '0'];
    $detail = updateStatus($where, $status);
    if ($detail) {
        $dr_id =  $list['dr_id'];
        $status = ['cash'=>['exp','cash+'.$clear_price], 'update_at' => time()];
        $detail =updateDrStatus(['id' => $dr_id], $status);
        if($detail){
            LogService::write('订单:' . $id, '订单结算成功'.$clear_price);
            $push_token = getDrPushToken($dr_id);
            if(!empty($push_token) && !empty($clear_price)){
                $titlepush = '有一笔订单结算成功';
                $contentpush = '订单:'.$list['order_code'].'，结算金额:'.$clear_price.'元';
                sendMsg($dr_id,$titlepush,$contentpush,1);
                pushInfo($push_token,$titlepush,$contentpush,'wztx_driver');//推送给司机
            }
        }else{
            LogService::write('订单:' . $id, '订单状态更改成功，基本信息表剩余金额更新失败');
        }
    } else {
        LogService::write('订单:' . $id, '订单结算失败');
    }
}
//修改订单状态
function updateStatus($where, $status) {
    $list = Db::name('TransportOrder')->where($where)->update($status);
    return $list;
}
//修改司机端认证态
function updateDrStatus($where, $status) {
    $list = Db::name('DrBaseInfo')->where($where)->update($status);
    return $list;
}
/*
* 得到通过id获取订单详情
*/
function getListOneInfo($where = []) {
    $list = Db::name('TransportOrder')->alias('a')->where($where)->field('a.*')->find();
    return $list;
}

// 保存推荐信息
function saveRecomm($where){
    if(empty($where['share_id']) || empty($where['share_id']) || !in_array($where['type'],['0','1'])){
        return false;
    }
    $whereExist = ['share_id'=>$where['share_id'],'invite_id'=>$where['invite_id'],'type'=>$where['type']];
    $isExist =  Db::name('ShareList')->where($whereExist)->find();
    if(!empty($isExist)){
        return false;
    }
    return Db::name('ShareList')->insert($where);
}

function getBaseSpUserInfo($sp_id){
    return Db::name('sp_base_info')->where("id",$sp_id)->find();
}
/**
 * Auther: guanshaoqiu <94600115@qq.com>
 * Describe:通过用户ID获取相关信息
 */
 function getBaseDrUserInfo($user_id){
     return Db::name('dr_base_info')->where("id",$user_id)->find();
}
/**
 * 获取设备或配置系统参数
 * @param string $name  参数名称
 * @param bool   $value 默认值
 * @return string|bool
 */
function getSysconf($name, $defaultValue = ''){
    $data = Db::name('system_config')->where(['name' => $name])->find();
    if(empty($data)){
        return $defaultValue;
    }
    return $data['value'];

}