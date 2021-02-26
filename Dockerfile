FROM php:8.0.2-cli

RUN mkdir /code

VOLUME /code

WORKDIR /code

#CMD ["ls"]
CMD [ "php", "-S", "0.0.0.0:80", "src/main.php" ]
