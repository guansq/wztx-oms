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

namespace think\model;

use think\Model;

class Pivot extends Model
{

<<<<<<< HEAD
    /**
     * 架构函数
     * @access public
     * @param array|object $data 数据
     * @param string $table 中间数据表名
     */
    public function __construct($data = [], $table = '')
    {
        if (is_object($data)) {
            $this->data = get_object_vars($data);
        } else {
            $this->data = $data;
        }

        $this->table = $table;
=======
    /** @var Model */
    public $parent;

    protected $autoWriteTimestamp = false;

    /**
     * 架构函数
     * @access public
     * @param Model         $parent 上级模型
     * @param array|object  $data 数据
     * @param string        $table 中间数据表名
     */
    public function __construct(Model $parent, $data = [], $table = '')
    {
        $this->parent = $parent;

        if (is_null($this->name)) {
            $this->name = $table;
        }

        parent::__construct($data);

        $this->class = $this->name;
>>>>>>> 43c1601fcae9771a4c23a155533aa4412a3a0d0e
    }

}
