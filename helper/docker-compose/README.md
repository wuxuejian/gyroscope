## Docker Compose 使用

### 1. 启动服务

在项目根目录执行以下命令

```shell
$ docker-compose up -d
```

在项目根目录执行以下命令, 通过端口地址可访问 `http://127.0.0.1:20300`

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
