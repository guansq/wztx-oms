<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BaseController {

    /**
     * 当前默认数据模型
     * @var string
     */
    protected $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    protected $title = '网站参数配置';

    /**
     * 显示系统常规配置
     */
    public function index() {
        if (!$this->request->isPost()) {
            parent::_list($this->table);
        } else {
            foreach ($this->request->post() as $key => $vo) {
                $org_config =  sysconf($key);
                sysconf($key, $vo);
                if(in_array($key,['storage_qiniu_secret_key'])){
                    $vo = '';
                }
                LogService::write('配置管理', $key.'从'.$org_config.'修改成'.$vo);
            }

            $this->success('数据修改成功！', '');
        }
    }

    /**
     * 文件存储配置
     */
    public function file() {
        $this->assign('alert', [
            'type' => 'success',
            'title' => '操作提示',
            'content' => '文件引擎参数影响全局文件上传功能，请勿随意修改！'
        ]);
        $this->title = '文件存储配置';
        $this->index();
    }

    /**
     * 保险费率设置
     */
    public function premium() {
        $this->title = '保险费率设置';
        $this->index();
    }

    //保证金设置
    public function bond() {
        $this->title = '保证金设置';
        $this->index();
    }

    //提现日期设置
    public function withdraw() {
        $this->title = '提现日期设置';
        $this->index();
    }

    //客服管理
    public function custommanage() {
        $this->title = '客服管理';
        $this->index();
    }

}
