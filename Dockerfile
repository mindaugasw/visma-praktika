FROM php:8.0.2-cli

RUN docker-php-ext-install pdo_mysql

RUN mkdir /code

WORKDIR /code

CMD [ "php", "-S", "0.0.0.0:80", "src/main.php" ]
