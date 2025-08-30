<p><h1 align="center">Веб-приложения для ООО "ВКС"</h1></p>

Исходные файлы Yii2-проекта официального веб-приложения для ООО "ВКС".

Версия Yii2-фреймворка:

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)

СТРУКТУРА ПРОЕКТА
-------------------

      assets/             содержит определение assets
      commands/           содержит консольные команды (контроллеры)
      components/         содержит дополнительные классы (компоненты) для работы с Google и Yandex таблицами
      config/             содержит общую конфигурацию приложения 
      controllers/        содержит контроллеры
      migrations/         содержит все миграции БД
      models/             содержит модели БД
      web/                содержит все веб-ресурсы сайта (стили, скрипты и т.д.)



ТРЕБОВАНИЯ
------------

Минимальное требование проекта - поддержка PHP 7.0 и выше


КОНФИГУРАЦИЯ
-------------

### СУБД PostrgeSQL

Отредактируйте файл `config/db.php`, например:

```php
return [
    'class' => 'yii\db\Connection',
        'dsn' => 'pgsql:host=localhost;port=5432;dbname=vksproject;',
        'username' => 'postgres',
        'password' => 'root',
        'charset' => 'utf8',
        'tablePrefix' => 'vks_',
        'schemaMap' => [
            'pgsql'=> [
                'class'=>'yii\db\pgsql\Schema',
                'defaultSchema' => 'public'
            ]
        ],
];
```