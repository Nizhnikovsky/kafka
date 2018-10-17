<<<<<<< HEAD
# Phalcon Clean Architecture Scaffold #

Данный репозиторий содержит базовый "скелет" проекта, архитектура которого сделана 
согласно требованиям Clean Architecture с использованием фреймворка Phalcon.

## Зависимости

#### Зависимости веб-сервера:

- [PHP](https://secure.php.net/) >= 7.0
- [PDO](https://secure.php.net/manual/en/book.pdo.php), [MySQL driver](https://secure.php.net/manual/en/ref.pdo-mysql.php)
- [php-yaml](https://secure.php.net/manual/en/book.yaml.php)
- [php-mbstring](https://secure.php.net/manual/en/book.mbstring.php)
- [php-json](https://secure.php.net/manual/en/book.json.php)
- [php-mcrypt](https://secure.php.net/manual/en/book.mcrypt.php)
- [Composer](https://getcomposer.org/)
- [Phalcon Framework](https://phalconphp.com/en/) >= 3.2
- [Redis](https://redis.io/), [php-redis](https://github.com/phpredis/phpredis)
- [Memcached](http://memcached.org/), [php-memcached](https://secure.php.net/manual/en/book.memcached.php)
- [libgearman](http://gearman.org/), [php-gearman](https://secure.php.net/manual/en/book.gearman.php)

#### Зависимости пакетов Composer

- [woxapp/phalcon-restful](https://bitbucket.org/WoxappGIT/libs_phalcon_restful/)
- [firebase/php-jwt](https://github.com/firebase/php-jwt)
- [swiftmailer/swiftmailer](https://github.com/swiftmailer/swiftmailer)
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)

#### Рекомендуемые инструменты

- [Phalcon Developer Tools](https://github.com/phalcon/phalcon-devtools)

## Проект

#### Установка

- Клонировать скелет с помощью команды:
```
git clone https://bitbucket.org/WoxappGIT/base_arch_php_phalcon/
```
- Перейти в директорию проекта:
```
cd ./base_arch_php_phalcon
```
- Установить необходимые зависимости с помощью Composer:
```
composer install
```
*Эта команда также проверит веб-сервер на наличие всех необходимых зависимостей.*

- Прописать необходимые подключения к БД и оперативным хранилищам в `app/Configuration/config.yml`

- С помощью поиска в коде проекта найти все подобные комментарии:

```
/* FIXME: Developers should change this according to the project. */
```
И изменить определенные строки кода, к которым были оставленны эти комментарии

- Изменить пространство имен с `Woxapp\Scaffold` на `Woxapp\[PROJECT]` - где `[PROJECT]` - название разрабатываемого проекта.

#### Структура проекта

Весь исходный код проекта должен хранится в директории `app`, и распределен по соответствующим
директориям слоев, к которым относится необходимый класс. Согласно принципам Clean Architecture,
проект разделяется на три слоя, которые независимы друг от друга, и взаимодействуют между собой 
через интерфейс. В данной структуре, эти слои отображены тремя директориями - `Presentation`, `Domain`, `Data`.
Ниже будет описано подробное назначение данных директорий. 

#### Dependency Injection

[DI](https://en.wikipedia.org/wiki/Dependency_injection) в данном проекте используется по стандартной механике паттерна Dependency Injection на языке PHP. Все зависимости
помещаются в DI контейнер используя анонимные функции, это позволяет сократить время инициализации приложения и всегда
загружать только те зависимости, которые требуются во время обработки конкретного запроса от клиента. В данном скелете,
DI контейнер находится в файле `app/Bootstrap.php`, в этом файле разработчик должен объявлять все новые сервисы, которые
будут реализованы в проекте. DI контейнер, объявленный в этом файле в будущем будет доступен по всему приложению.

#### Пространства имен

Все классы, которые добавляются к этому скелету должны находится в пространстве имен: `\Woxapp\[PROJECT_NAME]\[LAYER_NAME]`,
где `PROJECT_NAME` - имя проекта, `LAYER_NAME` - имя слоя соответственно. Пространства имен
всегда должны быть заданы согласно стандарту [PSR-4](http://www.php-fig.org/psr/psr-4/), и прописаны как в `composer.json` для автозагрузчика Composer,
так и в `app/Bootstrap.php` для автозагрузчика Phalcon. Стандартное пространтсво имен, которое прописано в данном репозитории
необходимо полностью заменить на вышеописанное, для того чтобы избежать путанницы в названиях.

#### Конфигурация

Конфигурация в проекте использует формат [YAML](http://yaml.org/), файл конфигурации загружается общий для проекта, происходит это
в `app/Bootstrap.php`, стандартно загружается файл `app/Configuration/config.yml`, который содержит все необходимые
настройки скелета и подключения к БД.

#### Обработка ошибок

Обработка всех ошибок в данном скелете реализована через встроенный в Phalcon компонент - [Event Manager](https://docs.phalconphp.com/en/3.2/events), ошибки происходят
при помощи исключений, в данном скелете уже сконфигурирован обработчик всех исключений - его точкой входа является специальный
контроллер - `app/Presentation/ErrorsController`, который при помощи функции `errorAction` обрабатывает любой `\Throwable`,
выброшенный при обработке запроса от клиента. Данный контроллер расширяется новыми функциями на усмотрение разработчика.

#### Cross-Origin Resource Sharing

Данный скелет поддерживает автоматические ответы для корректного функционирования запросов, которые сообщают
клиенту о том что определенные ресурсы в API доступны через CORS. Настройка CORS происходит следующим образом:

- При определении роута в `Routes.php`, необходимо обозначить, что добавляемый путь также будет доступен с помощью OPTIONS.

- При определении контроллера для обработки данного роута, контроллер необходимо унаследовать от `Woxapp\Scaffold\Presentation\AbstractController`

- `AbstractController` определяет одну абстрактную функцию - `corsMap(string $action)`, которую должен реализовать наследник.

- Функция `corsMap` должна в зависимости от переданного параметра `$action` возвращать массив, который содержит все разрешенные CORS заголовки для данного `$action`.

- Все остальные параметры и действия данный скелет совершает автоматически, включая получение разрешенных типов запроса, Origin хоста и значение параметра Credentials.

*Заметка: Разрешенные типы запроса получаются автоматически из объекта Router, Origin хост получается из текущего файла 
конфигурации (секция application.links, параметр origin), значение параметра Credentials задается в InternalController, константа CORS_ALLOW_CREDENTIALS*

#### Автоматический деплоймент

Данный скелет предусматривает любой способ автоматического деплоймента, который должен быть настроен и сконфигурирован
на усмотрение разработчика. Главным фактором тут является конфигурация проекта, которая находится в `app/Configuration/config.yml`,
и может быть распределена по нескольким "стейджам" проекта. Необходимой зависимостью является автоматическая ротация конфигураций,
которая должна быть выполнена при необходимости разделения проекта на "стейджи" на усмотрения разработчика.

#### Роутинг

Роутинг в данном скелете реализован согласно паттерну [Front Controller](https://en.wikipedia.org/wiki/Front_controller), новые роуты прописываются в файле `app/Routes.php`,
В проекте должен использоватся встроенный в Phalcon класс [Router](https://docs.phalconphp.com/en/3.2/routing), или же собственная реализация которая совместима с `\Phalcon\Mvc\RouterInterface`.

#### Точка входа, диспетчер запросов от клиента

Точкой входа является файл `public/index.php`, согласно требованиям данного скелета - `index.php` всегда должен
быть единственным файлом в публичной директории. Этот файл в первую очередь подключает `app/Bootstrap.php`, который
выполняет роль "инициализатора" всего проекта и описан выше. Затем, создается экземпляр класса `app/Application`, и управление
передается этому классу.

#### Application и диспетчер запросов

Данный скелет не использует встроенную в Phalcon реализацию Application по причине того, что Phalcon это MVC фреймворк,
и встроенный в фреймворк Application в конце каждого цикла работы диспетчера вызывает компонент View, для того чтобы рендерить
результат пользователю. Так как этот скелет предназначен в первую очередь для разработки REST API, компонент View нам не требуется,
мы обходимся одной функцией [json_encode](http://php.net/manual/en/function.json-encode.php). У данной реализации Application есть одна публичная функция - dispatch(), которая
принимает управление обработки запроса от клиента на себя, используя встроенный в фреймворк компонент - Dispatcher. Этот компонент
использует другой встроенный в Phalcon компонент - Router, для того чтобы распарсить URL, который запросил клиент, и в случае
если такой URL прописан в `app/Routes.php` - передать выполнение контроллеру, на который была назначена обработка данного URL.

#### Presentation Layer

Presentation слой является самым верхним слоем, который должен быть реализован по принципам Clean Architecture. Задача данного
слоя в этом скелете - обработка входящего запроса, валидация заголовков и тела запроса, и последующая передача нормализированных
данных в Domain Layer. Передача в Domain Layer происходит с помощью интерфейса `app/Domain/Interfaces/InteractorInterface`.

#### Domain Layer

Domain слой по принципам Clean Architecture является следующим слоем после Presentation, и содержит в себе определения бизнес-логики
приложения. Бизнес-логика распределена по логическим блокам, которые называются Use Case, и хранятся в директории `app/Domain/Usecase`.
В случае данного скелета - каждый Use Case должен реализовать `InteractorInterface`, который в свою очередь является интерфейсом,
содержащим Input и Output порты (это функции, через которые Presentation и Domain слои взаимодействуют между собой).
TODO: принцип работы input port, маппинг функций в юзкейсах

#### Data Layer

Data слой по принципам Clean Architecture является "последним" слоем, и являет собой реализацию паттерна проектирования - Repository,
Назначение Data слоя в этом скелете - работа со всеми подключенными к проекту хранилищами данных, включая реляционные БД (MySQL),
оперативные хранилища (Redis, Memcached), и другие используемые проектом хранилища. В отличии от Domain слоя, данный слой не содержит
конкретного интерфейса, и используется в Domain слое напрямую в Use Case. Работы с БД должна быть реализована через Phalcon ORM,
для генерации сущностей рекомендуется использовать [phalcon-devtools](https://github.com/phalcon/phalcon-devtools), все сущности хранятся в директории `app/Data/Entity`, и имеют
соответствующее пространство имен.

## Поставляемые сервисы

В связи с некоторыми ограничениями сервиса валидации, он поставляется вместе с скелетом проекта, а не отдельной библиотекой.

### Валидация

Сервис валидации в данном скелете состоит из нескольких частей, и является самым объемным сервисом, который предоставляется данным
набором библиотек. Весь модуль валидации является частью Presentation слоя и предназначен для использования только в нем.
Модуль валидации состоит из нескольких зависимых друг от друга частей:
- ValidationService - Сервис, который используется в контроллерах для валидации любых объектов или массивов

- ValidationFactory - Фабричный класс, основная обязанность которого - инициализировать `Phalcon\Validation` в зависимости от переданных параметров

- ValidatorFactory - Фабричный класс, основная обязанность которого - инициализировать правила валидации, которые использует `Phalcon\Validator` в зависимости от переданных параметров

- Validator - Кастомные валидаторы, которые были созданны внутри компании под определенные задачи, они также входят в `ValidatorFactory`, эта часть полностью расширяема (на усмотрение разработчика)

#### ValidationService

Сервис валидации реализует интерфейс `ValidationServiceInterface`, который имеет следующие функции:

`createValidation` - Использует фабрику `ValidationFactory` и создает объект `Phalcon\Validator` который является "ядром" данного сервиса.

**ВНИМАНИЕ**: Данная функция должна всегда вызываться при использовании функции `validate`, перед этой функцией **ВСЕГДА** должны устанавливатся
правила валидации (`setValidationRules`) и обязательные поля в валидируемом объекте (`setRequiredFields`). 

`setRequiredFields` - Функция, которая устанавливает набор параметров, которые обязательно должны присутствовать в таргетированной
сущности. **На переданные ключи в таргетированной сущности, правила валидации применяются только если они были переданы в** `setValidationRules`.  

`setOptionalFields` - Функция, которая устанавливает набор параметров, которые валидируются в таргетированной сущности только в том случае,
если они в ней присутствуют.

`setValidationRules` - Функция, которая принимает ассоциативный массив, в котором ключи являются именем поля в таргетированной сущности,
а значения - правилами валидации для значения по данному ключу.

**ВНИМАНИЕ**: У одного ключа может быть несколько правил валидации (их количество ничем не ограничивается), в случае нескольких правил валидации -
они передаются во вложенном массиве.

`validateRequestHeaders` - Специальная функция, которая используется для валидации заголовков запроса. При использовании этой функции,
функция `createValidation` вызывается автоматически, данная функция принимает только массив обязательных заголовков, и опционально,
массив необязательных заголовков. 

**ВНИМАНИЕ**: После того как функция завершила свою работу - состояние "ядра" сервиса, т.е. `Phalcon\Validator`
сбрасывается на **null**. 

`validate` - Валидирует переданный таргетированную сущность (ей может быть как массив, так и объект), используя правила валидации,
которые были заданы с помощью `setValidationRules`, валидацию проходят только те поля в таргетированной сущности, которые были
заданы с помощью `setRequireFields` или `setOptionalFields`. **Все остальные параметры, которые не были переданы этими
функциями, будут считатся валидатором неизвестными**.

#### Предоставляемые кастомные валидаторы

- `NullableValidator` - Валидатор, который проверяет только наличие параметра в таргетированном объекте. Значение не проверяется вовсе.

- `ArrayValidator` - Валидатор, который проверяет каждый элемент в таргетированном вложенном массиве. Проверяются значения по всем ключам массива, при этом ключи игнорируются.
=======
# Kafka_example

>>>>>>> 0bd142d12ce1889a20bec5fde8aec00ab295af4b
