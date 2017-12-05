<?php

namespace Luwz\Easemob\Facade;

use Illuminate\Support\Facades\Facade;
/**
 * Introduction 环信门面
 *
 * @author 刘维中
 * @email liu.wz@qq.com
 * @since 1.0
 * @date 2017-12-5
 */
class Easemob extends Facade
{
    /**
     * Introduction 门面服务注册器
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'easemob';
    }
    
}
