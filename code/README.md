<p><img style='width:30px;height:30px;' src="https://www.tuoluojiang.com/favicon.ico"><span style="font-size: 20px;font-weight: 600;margin-left: 10px;">陀螺匠 · 企业助手</span></p>

---

## 简介

企业管理系统

### 运行环境

1. PHP >= 8.0
2. BCMath PHP 拓展
3. Ctype PHP 拓展
4. Fileinfo PHP 拓展
5. JSON PHP 拓展
6. Mbstring PHP 拓展
7. OpenSSL PHP 拓展
8. PDO PHP 拓展
9. Tokenizer PHP 拓展
10. XML PHP 拓展
11. SWOOLE4 PHP扩展

### 主要特性

1. 采用最新的Laravel9.x+Swoole4框架开发
2. 标准接口、前后端分离
3. 降低流量高峰，解除耦合，高可用
4. 后台应用form-builder 无需写页面快速增删改查
5. 后台多种角色、多重身份权限管理，权限可以控制到每一步操作
6. 内置定时任务/异步任务
7. 内置长连接服务减少CPU及内存使用及网络堵塞，减少请求响应时长
8. 对接WPS在线协同编辑文档

### 相关命令

```shell
#命令
$ php bin/laravels {start|stop|restart|reload|info|help}

命令	  说明
start	  启动LaravelS,展示已启动的进程列表 "ps -ef|grep laravels"
stop	  停止LaravelS,并触发自定义进程的onStop方法
restart	  重启LaravelS,先平滑Stop,然后再Start.在Start完成之前,服务是不可用的
reload	  平滑重启所有Task/Worker/Timer进程,并触发自定义进程的onReload方法,不会重启Master/Manger进程,修改config/laravels.php后,你只有调用restart来完成重启
info	  显示组件的版本信息
help	  显示帮助信息

启动选项,针对start和restart命令.

选项	            说明
-d|--daemonize	    以守护进程的方式运行,此选项将覆盖laravels.php中swoole.daemonize设置
-e|--env	    指定运行的环境,如--env=testing将会优先使用配置文件.env.testing,这个特性要求Laravel 5.2+
-i|--ignore	    忽略检查Master进程的PID文件
-x|--x-version	    记录当前工程的版本号,保存在$_ENV/$_SERVER中,访问方式:$_ENV['X_VERSION'] $_SERVER['X_VERSION'] $request->server->get('X_VERSION')

```
### 修改代码后自动Reload(热更新：config/laravels.php文件中的hot_reload配置项)

### 安装流程

1. 执行:`composer install`安装扩展
2. 可选执行:`php artisan storage:link`创建本地文件映射软链
3. 执行:`php bin/laravels start -d`启动swoole服务(默认端口20200)
4. 按照文档配置反向代理 [Nginx反向代理参考文档](https://doc.tuoluojiang.com/doc/own/149)
5. 按照安装步骤完成安装

## Docker Compose 使用

### 1. 启动服务

在项目根目录执行以下命令, 通过端口地址可访问 `http://127.0.0.1:20300`

```shell
docker-compose up -d
```



#### 2. 配置文件参考(默认安装流程无需调整)

```
DB_HOST=192.168.10.14
DB_PORT=3306
DB_DATABASE=tuoluojiang
DB_USERNAME=root
DB_PASSWORD=123456

REDIS_HOST=192.168.10.15
REDIS_PASSWORD=123456
REDIS_PORT=6379
REDIS_DB=0
```

### 开发规范

#### 函数和类、属性命名

1. 类的命名采用驼峰法（首字母大写），例如 User、UserType；
2. common函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip；
3. 控制器里面的方法使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip
4. 方法的命名使用驼峰法（首字母小写），例如 getUserName；
5. 属性的命名使用驼峰法（首字母小写），例如 tableName、instance；
6. 特例：以双下划线__打头的函数或方法作为魔术方法，例如 __call 和 __autoload；

#### 常量和配置

1. 常量以大写字母和下划线命名，例如 APP_PATH；
2. 配置参数以小写字母和下划线命名，例如 url_route_on 和url_convert；
3. 环境变量定义使用大写字母和下划线命名，例如APP_DEBUG；

#### 数据表和字段

1. 数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 laraver_user 表和
   user_name字段，不建议使用驼峰和中文作为数据表及字段命名

## 参与开发人员

- 产品: 寇小雨

- 设计: xy-yyds

- 技术: Lunatic、夜风不是风、武湘军、娃哈哈

- 测试: 夏天、半山

## 帮助文档
https://www.tuoluojiang.com/doc/own/0
