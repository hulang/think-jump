<?php

declare(strict_types=1);

return [
    /*
     | 模板文件解析顺序（三项都留空时，用扩展包内置模板 src/tpl/*.html）：
     | 1. dispatch_success_tpl / dispatch_error_tpl：单个模板文件，绝对路径或相对应用根目录的路径
     | 2. tpl_path：模板目录，目录下按 success.html、error.html 取名
     | 3. 内置模板
     |
     | 注意：本文件安装时会被复制到应用的 config 目录，
     | 所以在默认值里不能出现 __DIR__ 或 dirname(__FILE__)，否则指向的是应用 config 目录。
     */

    // 自定义模板目录（留空用内置模板），建议指向应用目录
    // 例：app()->getAppPath() . 'view' . DIRECTORY_SEPARATOR . 'jump'
    'tpl_path' => '',

    // 成功跳转页面模板文件（留空则按 tpl_path/success.html 或内置 success.html 解析）
    'dispatch_success_tpl' => '',
    // 成功跳转页停留时间(秒)
    'default_success_wait' => 3,
    // 成功跳转 code 值
    'default_success_code' => 0,

    // 错误跳转页面模板文件（留空则按 tpl_path/error.html 或内置 error.html 解析）
    'dispatch_error_tpl' => '',
    // 错误跳转页停留时间(秒)
    'default_error_wait' => 3,
    // 错误跳转 code 值
    'default_error_code' => 1,

    // 默认输出类型
    'default_return_type' => 'html',
    // 默认 AJAX 请求返回数据格式，可用：Json,Jsonp,Xml
    'default_ajax_return' => 'json',
];
