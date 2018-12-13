# Introduction

####验证使用了Oauth

####介绍:http://www.ruanyifeng.com/blog/2014/05/oauth_2_0.html

####代码结构: 

Client: Token.php

Server: Server.php

####安装oAuth

###先安装Composer(Ci 3.1.0以上版本已经支持composer了)

url:https://pkg.phpcomposer.com/#how-to-install-composer

全局安装

```shell

curl -sS https://getcomposer.org/installer | php

mv composer.phar /usr/local/bin/composer

```

####注意： 如果上诉命令因为权限执行失败， 请使用 sudo 再次尝试运行 mv 那行命令

#####使用中国镜像包(全局安装)

```shell
composer config -g repo.packagist composer https://packagist.phpcomposer.com

更新

composer selfupdate

```

#####php低版本安装composer使用

先切换到你需要使用的项目中

本地创建 vim  install.sh文件

注意shell脚本里面的php版本 是你高版本的php版本 

```shell

#!/bin/sh

EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
RESULT=$?
rm composer-setup.php
exit $RESULT

```


####composer 安装 guzzle

Guzzle是一个PHP的HTTP客户端，用来轻而易举地发送请求，并集成到我们的WEB服务上。

* 接口简单：构建查询语句、POST请求、分流上传下载大文件、使用HTTP cookies、上传JSON数据等等。
* 发送同步或异步的请求均使用相同的接口。
* 使用PSR-7接口来请求、响应、分流，允许你使用其他兼容的PSR-7类库与Guzzle共同开发。
* 抽象了底层的HTTP传输，允许你改变环境以及其他的代码，如：对cURL与PHP的流或socket并非重度依赖，非阻塞事件循环。
* 中间件系统允许你创建构成客户端行为。


在这里搜索 https://packagist.org/

开始安装 进入到 项目下面（不要在root下安装）

```shell
composer require guzzlehttp/guzzle
```

####composer 安装 Oauth(验证)


```shell

composer require bshaffer/oauth2-server-php

```

    