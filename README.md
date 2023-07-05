## ThinkPHP 8.0.0 跳转扩展

### 环境

- php >=8.0.0
- ThinkPHP ^8.0

### 安装

```bash
composer require hulang/think-jump
```

### 生成配置

系统安装后会自动在 config 目录中生成 jump.php 的配置文件，
如果系统未生成可在命令行执行

```php
php think jump:config 
```

可快速生成配置文件

### 公共配置
```php
declare(strict_types=1);

return [
    // 成功跳转页面模板文件
    'dispatch_success_tpl'  => app()->getRootPath() . '/vendor/hulang/think-jump/src/tpl/success.html',
    // 成功跳转页停留时间(秒)
    'default_success_wait' => 3,
    // 成功跳转 code 值
    'default_success_code'  => 1,
    // 错误跳转页面模板文件
    'dispatch_error_tpl'    => app()->getRootPath() . '/vendor/hulang/think-jump/src/tpl/error.html',
    // 错误跳转页停留时间(秒)
    'default_error_wait'   => 3,
    // 错误跳转 code 值
    'default_error_code'    => 0,
    // 默认输出类型
    'default_return_type'   => 'html',
    // 默认 AJAX 请求返回数据格式，可用：Json,Jsonp,Xml
    'default_ajax_return'   => 'json',
];
```

Example
-------
在所需控制器内引用该扩展即可：

```php
<?php
namespace app\controller;

class Index 
{
    use \think\Jump; 
    public function index()
    {
        //return $this->error('error');
        //return $this->success('success','index/index');
        //return $this->redirect('/admin/index/index');
        return $this->result(['username' => 'byron sampson', 'sex' => '男']);  
    }
}

?>
```

下面示例我在框架自带的BaseController里引入，以后所有需要使用跳转的类继承自带的基类即可

以下是自带的基类
----------------------------
```php
<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    use \think\Jump;
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
}
```

这里继承BaseController后即可使用success、error、redirect、result方法

```php
<?php

namespace app\admin\controller;

class Index extends \app\BaseController
{
    public function demo1()
    {
        /**
         * 操作成功跳转的快捷方法
         * @param  mixed $msg 提示信息
         * @param  string $url 跳转的URL地址
         * @param  mixed $data 返回的数据
         * @param  integer $wait 跳转等待时间
         * @param  array $header 发送的Header信息
         */
        // 一般用法
        return $this->success('登录成功', 'index/index');
        //完整用法
        //return $this->success($msg = '登录成功',  $url = 'index/index', $data = '',  $wait = 3,  $header = []);
    }

    public function demo2()
    {
        /**
         * 操作错误跳转的快捷方法
         * @param  mixed $msg 提示信息
         * @param  string $url 跳转的URL地址
         * @param  mixed $data 返回的数据
         * @param  integer $wait 跳转等待时间
         * @param  array $header 发送的Header信息
         */
        // 一般用法
        return $this->error('登录失败');
        //return $this->success('登录失败','login/index');
        //完整用法
        //return $this->error($msg = '登录失败',  $url = 'login/index', $data = '',  $wait = 3,  $header = []);

    }

    public function demo3()
    {
        /**
         * URL重定向
         * @param  string $url 跳转的URL表达式
         * @param  integer $code http code
         * @param  array $with 隐式传参
         */
        //一般用法
        //第一种方式：直接使用完整地址（/打头）
        //return $this->redirect('/admin/index/index');
        //第二种方式：如果你需要自动生成URL地址，应该在调用之前调用url函数先生成最终的URL地址。
        return $this->redirect(url('index/index', ['name' => 'think']));
        //return $this->redirect('http://www.thinkphp.cn');
        //完整用法
        //return $this->redirect($url= '/admin/index/index', $code = 302, $with = ['data' => 'hello']);
    }

    public function demo4()
    {
        /**
         * 返回封装后的API数据到客户端
         * @param  mixed $data 要返回的数据
         * @param  integer $code 返回的code
         * @param  mixed $msg 提示信息
         * @param  string $type 返回数据格式
         * @param  array $header 发送的Header信息
         */
        //一般用法
        return $this->result(['username' => 'byron sampson', 'sex' => '男']);
        //完整用法
        //return $this->result($data=['username' => 'byron sampson', 'sex' => '男'], $code = 0, $msg = '', $type = '',  $header = []); 
    }
}
```
