# Базовые миграции для фреймворка HLEB
Эти миграции реализуют минимальный набор стандартных действий, основное их предназначение - выполнить заготовленные запросы и зафиксировать в базе данных факт их выполнения, предотвращая повторное.
Для этого во фреймворке должна быть подключена база данных.

#### Установка
```bash
$ composer require phphleb/migration
```
#### Развертывание
```bash
$ php console phphleb/migration --add
```

_Ввиду того, что откат и удаление/выполнение отдельных миграций редко применяется в практической разработке, этих функций нет в текущей библиотеке. Если возникнет крайняя на то необходимость,
то можно удалить конкретные индексы миграций(label) из таблицы migrations (создаётся автоматически) и перезапустить выполнение миграций. Но это не отменит изменения, внесенные миграциями в БД.
Такие действия не являются общепринятыми при работе с миграциями и указаны для крайних несвойственных нормальной работе миграций случаев. Концепция миграций не подразумевает, что последовательное их добавление будет каким-то образом нарушено.
Для отмены миграций можно написать новую миграцию поверх остальных, отменяющую изменения одной из предшествующих._

Теперь о том, что реализовано в библиотеке.

Можно создать шаблон миграции при помощи консольной команды:
```bash
$ php console migration/create example_name
```

Эта команда создаст в папке `migrations` проекта новую миграцию с именем 'Migration_XXX_example_name.php' (где индекс ХХХ - текущее UNIX-время в миллисекундах). Если папки `migrations` нет в
корневой директории проекта, то нужно создать её вручную. Если эта папка вас не устраивает, нужно будет переписать классы консольных команд с указанием директории
и использовать собственные, развертывание библиотеки при этом можно не применять.

В классе 'Migration_XXX_example_name' файла миграции один метод **run**(), в который можно добавить один или более SQL-запросов с помощью метода **addSql**(...)

```php
<?php

class Migration_XXX_example_name extends \Phphleb\Migration\Src\BaseMigrate
{
   public function run()
   {
      $this->addSql("INSERT INTO table_name (cell_name) VALUES ('example_value')");
   }
}

```

Также есть команда для выполнения миграций:

```bash
$ php console migration/run
```

Выполнит все незафиксированные миграции (ранее не выполненные) и зафиксирует их в таблице `migrations` базы данных, последняя указана в настройках проекта как основная. 
Соответственно, все SQL-запросы миграций могут быть выполнены только к этой БД. Для второстепенной базы данных из конфигурации также можно дополнительно создать свои консольные
команды на создание и выполнение с указанием конкретного названия БД.

В случае, если вы хотите добавить миграции на уже существующий проект с базой данных некоторого размера, создайте первую миграцию из дампа структуры этой БД, 
добавив к созданию таблиц выражение 'IF NOT EXISTS', после чего выполните миграции.

#### Обновление

```bash
$ composer update phphleb/migration
$ php console phphleb/migration --add
$ composer dump-autoload
```

-----------------------------------


[![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/draft/blob/main/LICENSE) ![PHP](https://img.shields.io/badge/PHP-^7.1.0-blue) ![PHP](https://img.shields.io/badge/PHP-8-blue)
