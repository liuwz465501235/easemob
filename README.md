## 安装教程

Run ``` 在项目根目录运行命令: composer require luwz/easemob ```

Add ``` 在config/app.php的providers组下添加: Luwz\Easemob\EasemobServiceProvider::class ```

Add ``` 在config/app.php的aliases组下添加: 'Easemob' => Luwz\Easemob\Facades\Easemob::class```

Run ``` 在项目根目录运行命令: php artisan vendor:publish ```

Setting ``` 在config/easemob.php文件配置相关参数 ```

