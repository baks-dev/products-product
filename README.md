# BaksDev Product

[![Version](https://img.shields.io/badge/version-7.0.32-blue)](https://github.com/baks-dev/products-product/releases)
![php 8.2+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль Продукции

## Установка

``` bash
$ composer require baks-dev/products-product
```

## Дополнительно

Установка файловых ресурсов в публичную директорию (javascript, css, image ...):

``` bash
$ php bin/console baks:assets:install
```


Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

Тесты

``` bash
$ php bin/phpunit --group=products-product
```


## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
