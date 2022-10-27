<?php

declare(strict_types=1);

return [
    // 默认成功 code
    'default_success_code'  => 1,
    // 默认失败 code
    'default_error_code'    => 0,
    // 默认输出类型
    'default_return_type'   => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'   => 'json',
    // 默认跳转页面对应的模板文件
    'dispatch_success_tpl'  => app()->getRootPath() . '/vendor/hulang/think-jump/src/tpl/dispatch_jump.tpl',
    'dispatch_error_tpl'    => app()->getRootPath() . '/vendor/hulang/think-jump/src/tpl/dispatch_jump.tpl',
];
