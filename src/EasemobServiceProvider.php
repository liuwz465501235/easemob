<?php

namespace Luwz\Easemob;

use Illuminate\Support\ServiceProvider;

/**
 * Introduction 环信服务注册类
 *
 * @author 刘维中
 * @email liu.wz@qq.com
 * @since 1.0
 * @date 2017-12-5
 */
class EasemobServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/easemob.php' => config_path('easemob.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('easemob', function(){
            return new Easemob();
        });
    }
}
