## db 的设计以社区为原型，公众号文章只是个变种

php -S 0.0.0.0:80 -t public

```bash
docker run --name mbapi -p 8088:80 --link local-mysql:local-mysql --link weapp-redis:weapp-redis -v /Users/danielwoo/self_workspace/meessage-board/meessageboardApi:/var/www/app/ -d romeoz/docker-nginx-php:7.2
```

```bash
docker exec -it quan_api php artisan queue:work redis --queue=upload --tries=3 --sleep=10
```

```bash
docker build -t quan-queue-image -f deploy/queue/Dockerfile deploy/queue
```

```bash
docker run --name quan-queue -v /var/www/api/:/usr/src/app --link myredis:myredis --link mysql:mysql -d pensacola1989/quan-queue-image:v1

```

```bash
ps aux | grep php
```

### transfomer 里掉\$this->item 只能在 includeXxx 中使用，否则使用 fractal()

✗ ssh -p 22 root@115.28.107.124

## 审核一个圈子

```bash
php artisan place:approve --pid={pid}
php artisan system:notify --nid={nid}
```

## 格式化时间成对出现

```php
'created_at' => $placeSubApply->created_at ? $placeSubApply->created_at->toDateTimeString() : null,
'timeDiff' => timeDiffForHuman(\$placeSubApply->created_at)
```

```bash
# wechat notify queue
php artisan queue:work redis --queue=weChatNotify --tries=3 --sleep=10
```

## todo，根据优先级

1.每个圈子都有配置项目，“开启发帖”，“开启互评”，“开启匿名（匿名可能没法做，政策）”;

2.该 cache（缓存） 的 cache，上 swoole 提速;

3.举报需要附带详情；

3.增加平台级的配置文件，如“全局禁止发帖”，“全局禁止评论”，“强制某个圈子不可见”（政策应急），此配置文件优先级高于单个圈子;

## 按热度排序不能用 sinceId 和 maxId，因为按热度查询，Id 是无序的

## swoole_api docker container

```bash
docker run --name swoole_api --link myredis:myredis --link mysql:mysql -p 8052:80 -v /var/www/swoole_mp_circle_api/:/var/www/app -d pensacola1989/swoole-api-image:v9
```

https://nickjanetakis.com/blog/docker-tip-65-get-your-docker-hosts-ip-address-from-in-a-container

## queue

```bash
docker run --name mp-circle-queue --link myredis:myredis --link mysql:mysql  -v /var/www/mpCircleApi/:/usr/src/app -d pensacola1989/quan-queue-image:v2
```

## Upload

上传部分被 nginx 转发到 fpm 模式了，因为 queue 里没有 swoole 的环境，懒得在 queue 的容器里整 swoole 环境了

## 发布

发布需要先合并到 deploy（swoole 运行时）分支

## 查询每个板子下面对应用户的未来帖子数量

```sql

SELECT
	COUNT(if(hv.updated_at < posts.created_at,1, null)) AS count,
	hv.user_id,
-- 	posts.place_id,
	places.*
FROM
	history_views hv
	LEFT JOIN places ON hv.place_id = places.id
	LEFT JOIN posts ON hv.place_id = posts.place_id
WHERE
	hv.user_id = 17
GROUP BY
	places.id
-- 	posts.place_id
ORDER BY count desc;

-- 聚合的话如果用了posts.place_id，没有意义了，但是可以用places.id做group by

```

启动php cli
```bash
php -S 0.0.0.0:80 -t public
```

php.ini位置
```bash
php -i |grep php\.ini
```
php扩展
```bash
docker-php-ext-install
docker-php-ext-install pdo pdo_mysql
```

## vscode dockerfile back

```yml
#-------------------------------------------------------------------------------------------------------------
# Copyright (c) Microsoft Corporation. All rights reserved.
# Licensed under the MIT License. See https://go.microsoft.com/fwlink/?linkid=2090316 for license information.
#-------------------------------------------------------------------------------------------------------------

FROM php:7-cli

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive

# This Dockerfile adds a non-root user with sudo access. Use the "remoteUser"
# property in devcontainer.json to use it. On Linux, the container user's GID/UIDs
# will be updated to match your local UID/GID (when using the dockerFile property).
# See https://aka.ms/vscode-remote/containers/non-root-user for details.
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# Configure apt and install packages
RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils dialog 2>&1 \
    #
    # install git iproute2, procps, lsb-release (useful for CLI installs)
    && apt-get -y install git iproute2 procps iproute2 lsb-release \
    #
    # Install xdebug
    && yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    #
    # Create a non-root user to use if preferred - see https://aka.ms/vscode-remote/containers/non-root-user.
    && groupadd --gid $USER_GID $USERNAME \
    && useradd -s /bin/bash --uid $USER_UID --gid $USER_GID -m $USERNAME \
    # [Optional] Add sudo support for the non-root user
    && apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME\
    && chmod 0440 /etc/sudoers.d/$USERNAME \
    #
    # Clean up
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=dialog



```