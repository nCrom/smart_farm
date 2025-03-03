# PHP 8.2 Apache 베이스 이미지 사용
FROM php:8.2-apache

# 라이믹스 CMS에 필요한 PHP 확장 기능 설치
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql mysqli zip xml simplexml curl opcache

# 메모리 제한 증가 및 업로드 크기 제한 증가
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/upload-limit.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/upload-limit.ini

# Apache 모듈 활성화
RUN a2enmod rewrite headers

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 권한 설정
RUN chown -R www-data:www-data /var/www/html
