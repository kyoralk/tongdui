<?php
// 应用入口文件
// 检测PHP环境
//if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('APP_DEBUG',true);// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('WWWROOT',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");//网站根目录
define('APP_PATH','./Application/');// 定义应用目录
define('RUNTIME_PATH','./Runtime/');// 定义运行时目录
define('COMMON_PATH','./Common/');
require './ThinkPHP/ThinkPHP.php';