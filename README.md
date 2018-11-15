# Install
1: nginx 去除 index.php:

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
2:请配置环境变量  SERVER_ENV 

    fastcgi_param  SERVER_ENV 'development'; # product or development
    
