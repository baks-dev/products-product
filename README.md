# BaksDev Products

![Version](https://img.shields.io/badge/version-v0.0.1-blue) ![php 8.1+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль продукции

## Установка

Модуль должен быть расположен в директории *src/Module/Products/Products*

для автоматической установки в указанную директорию в коревом файле проекта composer.json необходимо указать следующие
зависимости и затем выполнить комманду установки:

![json](https://img.shields.io/badge/Json-green)

``` json
{
    "require": {
        "oomphinc/composer-installers-extender": "*"
    },
    
    "require-dev": {
        "roave/security-advisories": "dev-latest"
    },
    
    "config": {
        "allow-plugins": {
            "oomphinc/composer-installers-extender": true,
            "composer/installers": true
        }
    },

    "extra": {
        "installer-types": ["library"],
        "installer-paths": {
            "src/Module/Products/Products": ["baks-dev/products-product"]
        }
    }
}
```

![Install](https://img.shields.io/badge/composer-green)

``` bash
$ composer require baks-dev/products-product
```

## Журнал изменений ![Changelog](https://img.shields.io/badge/changelog-yellow)

О том, что изменилось за последнее время, обратитесь к [CHANGELOG](CHANGELOG.md) за дополнительной информацией.

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.


