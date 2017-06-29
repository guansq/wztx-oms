<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\cache\Driver;

class Cache
{
    protected static $instance = [];
    public static $readTimes   = 0;
    public static $writeTimes  = 0;

    /**
     * 操作句柄
     * @var object
     * @access protected
     */
    protected static $handler;

    /**
     * 连接缓存
     * @access public
     * @param array         $options  配置数组
     * @param bool|string   $name 缓存连接标识 true 强制重新连接
     * @return Driver
     */
    public static function connect(array $options = [], $name = false)
    {
        $type = !empty($options['type']) ? $options['type'] : 'File';
        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($type, '\\') ? $type : '\\think\\cache\\driver\\' . ucwords($type);

            // 记录初始化信息
            App::$debug && Log::record('[ CACHE ] INIT ' . $type, 'info');
            if (true === $name) {
                return new $class($options);
            } else {
                self::$instance[$name] = new $class($options);
            }
        }
<<<<<<< HEAD
        self::$handler = self::$instance[$name];
        return self::$handler;
=======
        return self::$instance[$name];
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 自动初始化缓存
     * @access public
     * @param array         $options  配置数组
<<<<<<< HEAD
     * @return void
=======
     * @return Driver
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            // 自动初始化缓存
            if (!empty($options)) {
<<<<<<< HEAD
                self::connect($options);
            } elseif ('complex' == Config::get('cache.type')) {
                self::connect(Config::get('cache.default'));
            } else {
                self::connect(Config::get('cache'));
            }
        }
=======
                $connect = self::connect($options);
            } elseif ('complex' == Config::get('cache.type')) {
                $connect = self::connect(Config::get('cache.default'));
            } else {
                $connect = self::connect(Config::get('cache'));
            }
            self::$handler = $connect;
        }
        return self::$handler;
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex
     * @access public
     * @param string $name 缓存标识
     * @return Driver
     */
<<<<<<< HEAD
    public static function store($name)
    {
        if ('complex' == Config::get('cache.type')) {
            self::connect(Config::get('cache.' . $name), strtolower($name));
        }
        return self::$handler;
=======
    public static function store($name = '')
    {
        if ('' !== $name && 'complex' == Config::get('cache.type')) {
            return self::connect(Config::get('cache.' . $name), strtolower($name));
        }
        return self::init();
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public static function has($name)
    {
<<<<<<< HEAD
        self::init();
        self::$readTimes++;
        return self::$handler->has($name);
=======
        self::$readTimes++;
        return self::init()->has($name);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存标识
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function get($name, $default = false)
    {
<<<<<<< HEAD
        self::init();
        self::$readTimes++;
        return self::$handler->get($name, $default);
=======
        self::$readTimes++;
        return self::init()->get($name, $default);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 写入缓存
     * @access public
     * @param string        $name 缓存标识
     * @param mixed         $value  存储数据
     * @param int|null      $expire  有效时间 0为永久
     * @return boolean
     */
    public static function set($name, $value, $expire = null)
    {
<<<<<<< HEAD
        self::init();
        self::$writeTimes++;
        return self::$handler->set($name, $value, $expire);
=======
        self::$writeTimes++;
        return self::init()->set($name, $value, $expire);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public static function inc($name, $step = 1)
    {
<<<<<<< HEAD
        self::init();
        self::$writeTimes++;
        return self::$handler->inc($name, $step);
=======
        self::$writeTimes++;
        return self::init()->inc($name, $step);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public static function dec($name, $step = 1)
    {
<<<<<<< HEAD
        self::init();
        self::$writeTimes++;
        return self::$handler->dec($name, $step);
=======
        self::$writeTimes++;
        return self::init()->dec($name, $step);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 删除缓存
     * @access public
     * @param string    $name 缓存标识
     * @return boolean
     */
    public static function rm($name)
    {
<<<<<<< HEAD
        self::init();
        self::$writeTimes++;
        return self::$handler->rm($name);
=======
        self::$writeTimes++;
        return self::init()->rm($name);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     */
    public static function clear($tag = null)
    {
<<<<<<< HEAD
        self::init();
        self::$writeTimes++;
        return self::$handler->clear($tag);
=======
        self::$writeTimes++;
        return self::init()->clear($tag);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 读取缓存并删除
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public static function pull($name)
    {
<<<<<<< HEAD
        self::init();
        self::$readTimes++;
        self::$writeTimes++;
        return self::$handler->pull($name);
=======
        self::$readTimes++;
        self::$writeTimes++;
        return self::init()->pull($name);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param int       $expire  有效时间 0为永久
     * @return mixed
     */
    public static function remember($name, $value, $expire = null)
    {
<<<<<<< HEAD
        self::init();
        self::$readTimes++;
        return self::$handler->remember($name, $value, $expire);
=======
        self::$readTimes++;
        return self::init()->remember($name, $value, $expire);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

    /**
     * 缓存标签
     * @access public
     * @param string        $name 标签名
     * @param string|array  $keys 缓存标识
     * @param bool          $overlay 是否覆盖
     * @return Driver
     */
    public static function tag($name, $keys = null, $overlay = false)
    {
<<<<<<< HEAD
        self::init();
        return self::$handler->tag($name, $keys, $overlay);
=======
        return self::init()->tag($name, $keys, $overlay);
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

}
