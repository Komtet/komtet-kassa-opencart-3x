## Запуск проекта

* Склонируйте репозиторий включая подмодули для подтягивания SDK - git clone --recurse-submodules
* Скачать установщик Opencart CMS - http://opencart-russia.ru/
* Распаковать архив OpenCart CMS в корневой каталог и переименовать upload-x-x в php
* Переименовать файлы: /php/config-dist.php и /php/admin/config-dist.php в config.php
* Запустить сборку проекта
```sh
make build
```

## Установка CMS

* Запустить контейнер
```sh
make start_web7
```
* Установить права на папку php
```sh
sudo chmod -R 777 php
```
* Проект будет доступен по адресу: localhost:8000;
* Настройки подключения к бд MySQL:
```sh
Сервер: mysql
Пользователь: devuser
Пароль: devpass
БД: opencart_db
Порт: 3306
```
* Следует добавить администратора системы
* После завершения установки необходимо удалить установочную директорию /php/install

## Установка модуля КОМТЕТ КАССЫ для Opencart 3.x

* Необходимо [Скачать архив](https://github.com/Komtet/komtet-kassa-opencart/releases) 
* Файл должен называться `komtet-kassa-<version>.ocmod.zip`, где `<version>` &mdash; это версия модуля.
* Необходиомо перейти в раздел Модули/Расширения >> Установка расширений
* Загрузить скачанный архив
* Перейти Модули/Расширения >> Модули/Расширения найти установленный модуль, его активировать и настроить
* Перейти Модификаторы и нажать обновить

## Доступные команды из Makefile

* Собрать проект
```sh
make build
```
* Запустить проект на php5.6
```sh
make start_web5
```

* Запустить проект на php7.3
```sh
make start_web7
```

* Остановить проект
```sh
make stop
```

* Обновить модуль в cms
```sh
make update
```

* Подготовить архив для загрузки в маркет
```sh
make market_release
```

* Подготовить архив для ручной установки плагина через CMS
```sh
make release
```

* Версия проекта
```sh
make version
```

[Версия для Opencart 2.3](https://github.com/Komtet/komtet-kassa-opencart/tree/opencart-2.3)
