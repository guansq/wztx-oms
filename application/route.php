<?php

use think\Route;

Route::get('financial/tcStatistics','Financial/tcStatistics');
Route::get('financial/rechargeRecord','Financial/rechargeRecord');
return [
    //-------------------
    //  __domain__  域名部署
    //-------------------
    '__domain__' => [
        //'atwwg.api'      => 'api',
        'wztx.oms' => 'admin',
        //'atwwg.spl'      => 'spl'
    ],
    '__rest__' => [
        'ad' => 'Ad',
        'driver' => 'Driver',
        'shipper' => 'Shipper',
        'financial' => 'Financial',
        'withdrawal' => 'Withdrawal',
        'share' => 'Share',
        'orderComment' => 'OrderComment',
    ],
];
