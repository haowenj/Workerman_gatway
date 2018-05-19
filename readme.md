//目录结构

Applications 具体的逻辑代码

logs 日志

vendor 第三方依赖包，包含Workman源码

.env.example 配置文件案例

start.php 启动脚本

配置文件中使用的是Workman自带的SSL功能，一下是基于Nginx代理实现SSL的相关说明
,Workman官方文档也有说明

1、.env 禁用SSL

2、添加Nginx配置文件
````
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    
    server_name host.com;
    ssl_certificate      ****.pem;
    ssl_certificate_key  ****.key;
    ssl_session_timeout 5m;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    
    location / {
        proxy_pass http://127.0.0.1:8282;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header X-Real-IP $remote_addr;
    }
    
    error_page  404              /404.html;
    location = /50x.html {
            root   html;
    }
}
````