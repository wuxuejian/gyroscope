FROM registry.cn-hangzhou.aliyuncs.com/llfei/crmeb AS tuoluojiang

# 设置容器启动后的默认运行目录
WORKDIR /var/www

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
