<?php

declare(strict_types=1);

namespace think;

use think\exception\HttpResponseException;
use think\Response;

trait Jump
{
    /**
     * 应用实例
     * @var mixed|\think\App
     */
    protected $app;

    /**
     * 操作成功后的跳转提示方法
     * 
     * 该方法用于在操作成功后,给用户一个反馈,并跳转到指定的页面
     * 支持跳转到绝对URL、相对URL或返回上一页.同时,可以携带额外的数据和自定义跳转等待时间
     * 
     * @param mixed $msg 提示信息,可以是字符串或数组
     * @param string $url 跳转的URL地址,如果不指定,则默认返回上一页
     * @param mixed $data 返回的数据,通常用于前端进一步处理
     * @param integer $wait 跳转等待时间,单位为秒
     * @param array $header 发送的HTTP头信息,用于自定义响应头
     * @return mixed|void
     * 
     * @access protected
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {

        if (is_null($url) && isset($_SERVER['HTTP_REFERER'])) {
            // 如果URL为空,且存在HTTP_REFERER,则使用HTTP_REFERER作为跳转URL
            $url = $_SERVER['HTTP_REFERER'];
        } elseif ($url) {
            // 如果URL不为空,但不是绝对URL,則构建应用内的URL
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        // 构建跳转结果数组,包含代码、消息、数据、URL和等待时间
        $result = [
            'code' => $this->app->config->get('jump.default_success_code', 0),
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        // 根据响应类型,创建相应的响应对象
        $type = $this->getResponseType();
        if ('html' == strtolower($type)) {
            // 如果是HTML响应,使用模板渲染跳转页面
            $response = Response::create($this->app->config->get('jump.dispatch_success_tpl', $type), 'view')
                ->assign($result)->header($header);
        } else {
            // 对于其他类型的响应,直接创建JSON或XML等类型的响应
            $response = Response::create($result, $type)->header($header);
        }
        // 抛出HTTP响应异常,触发跳转或数据返回
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * 此方法用于处理操作错误的情况,提供了一种统一的错误响应方式,可以适用于不同的请求类型(如AJAX或普通请求)
     * 它允许开发人员设置错误消息、错误跳转的URL、返回的数据、跳转等待时间以及额外的HTTP头信息
     * @access protected 保护级别访问,通常由类的内部方法调用
     * @param mixed $msg 错误消息,可以是字符串或任何可打印的类型
     * @param string $url 错误跳转的URL地址,如果为null,则根据请求类型决定跳转行为
     * @param mixed $data 返回的数据,可以是任何类型
     * @param integer $wait 跳转等待时间,单位为秒
     * @param array $header 发送的HTTP头信息,允许开发人员自定义HTTP响应头
     * @return mixed|void 返回值取决于响应类型,对于HTML类型,返回渲染后的错误页面;对于其他类型,返回一个包含错误信息的响应对象
     */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            // 根据是否为AJAX请求,决定错误跳转的URL,默认为返回上一页
            $url = $this->app->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            // 如果提供了URL,则根据URL的形式进行处理,确保URL的正确性
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        // 构建错误响应数组,包含错误代码、消息、数据、跳转URL和等待时间
        $result = [
            'code' => $this->app->config->get('jump.default_error_code', 1),
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];
        // 根据配置和请求类型,决定响应的类型
        $type = $this->getResponseType();
        // 如果是HTML类型响应,则使用配置的错误模板进行渲染
        if ('html' == strtolower($type)) {
            $response = Response::create($this->app->config->get('jump.dispatch_error_tpl', $type), 'view')
                ->assign($result)->header($header);
            // 对于其他类型的响应,直接创建一个包含错误信息的响应对象
        } else {
            $response = Response::create($result, $type)->header($header);
        }
        // 抛出一个HTTP响应异常,用于中断当前执行并返回错误响应
        throw new HttpResponseException($response);
    }

    /**
     * 封装API返回数据
     * 该方法用于统一构建API的响应数据格式,包括返回码、消息、时间和数据部分
     * 根据配置和请求需要,可以返回不同格式的数据(如JSON、XML等)
     * 
     * @param mixed $data 要返回给客户端的数据
     * @param integer $code 返回码,用于表示操作的状态或结果
     * @param mixed $msg 提供给客户端的详细消息或说明
     * @param string $type 返回数据的类型,用于指定响应的数据格式
     * @param array $header 需要添加到响应中的HTTP头部信息
     * @return mixed|void 返回封装好的响应数据,或者直接抛出HttpResponseException
     */
    protected function result($data, $code = 0, $msg = '', $type = '', array $header = [])
    {
        // 构建返回数据的基本结构,包括返回码、消息、时间和数据
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => time(),
            'data' => $data,
        ];
        // 根据传入的类型或者默认配置,确定最终的返回数据类型
        $type = empty($type) ? $this->app->config->get('jump.default_ajax_return', 'json') : $this->getResponseType();
        // 创建响应对象,并设置数据类型和头部信息
        $response = Response::create($result, $type)->header($header);
        // 抛出响应对象,结束当前请求
        throw new HttpResponseException($response);
    }

    /**
     * 重定向到另一个URL
     * 
     * 该方法用于将当前请求重定向到指定的URL.它支持不同的HTTP重定向状态码
     * 允许携带额外的参数一起重定向
     * 
     * @param string $url 重定向的目标URL
     * @param int $code HTTP重定向状态码,默认为302,表示临时重定向
     *                  可以使用其他状态码,如301(永久重定向)等
     * @param array $with 伴随重定向传递的参数,这些参数将会附加到目标URL上
     * @throws HttpResponseException 抛出一个包含重定向响应的异常
     */
    protected function redirect($url = '', $code = 302, $with = [])
    {
        // 创建一个重定向响应对象,并设置目标URL和重定向状态码
        $response = Response::create($url, 'redirect');
        // 设置重定向状态码,并携带额外参数
        $response->code($code)->with($with);
        // 抛出包含重定向响应的异常,中断当前执行并进行重定向
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前请求的响应类型
     * 
     * 本方法用于确定应以何种格式返回响应数据.它基于当前请求是否为JSON或AJAX请求来作出决策
     * 如果请求是JSON或AJAX类型,它将返回配置中指定的默认JSON返回类型;否则,它将返回默认的HTML返回类型
     * 这种区分有助于支持不同的数据交互模式,例如,前端页面请求通常返回HTML,而API请求则通常返回JSON
     * 
     * @access protected 保护级别访问,仅允许子类和本类调用
     * @return mixed|string 返回值可以是字符串类型的'json'或'html',或者根据配置返回更具体的类型
     */
    protected function getResponseType()
    {
        // 判断当前请求是否为JSON或AJAX请求
        if ($this->app->request->isJson() || $this->app->request->isAjax()) {
            // 如果是,返回默认的AJAX响应类型,优先从配置中获取,如果未配置,则默认为'json'
            return $this->app->config->get('jump.default_ajax_return', 'json');
        } else {
            // 如果不是AJAX请求,返回默认的HTML响应类型,优先从配置中获取,如果未配置,则默认为'html'
            return $this->app->config->get('jump.default_return_type', 'html');
        }
    }
}
