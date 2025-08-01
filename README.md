# Оптимизация картинок и конвертация в webp/avif - автоматически и без сторонних сервисов
Модуль, который делает оптимизацию jpeg/png/svg/gif картинок и pdf-файлов для 1С-Битрикс.
Также модуль делает конвертацию jpeg/png картинок в webp/avif для 1С-Битрикс.

Модуль доступен в [маркетплейсе битрикс](http://marketplace.1c-bitrix.ru/solutions/dev2fun.imagecompress/).

## Что оптимизирует модуль:
* картинки jpeg
* картинки png
* файлы pdf
* картинки gif
* картинки svg

## В какие форматы происходит конвертация
* webp
* avif

## Что конвертируется в webp и/или в avif:
* картинки jpeg
* картинки png

## Режимы конвертаций:
* hitConvert - конвертация "на лету" при запросе картинки или её ресайза.
* postConvert - конвертация "на лету" перед показом страницы. Кэшируется.
* lazyConvert - ленивая (отложенная) конвертация. Конвертация происходит постепенно в фоне. Кэшируется. **Рекомендуется!**
> можно использовать hitConvert+postConvert

## Преимущества модуля:

* оптимизация картинок
* конвертация картинок
* модуль использует рекомендуемые google: jpegoptim, optipng, gs, svgo, gifsicle, cwebp, avif
* сжатие картинок в 5-10 раз
* пройдете анализ PageSpeed Insights
* автоматическая оптимизация (на лету)
* оптимизация картинок при ресайзе (на лету)
* автоматическая конвертация картинок в webp (на лету или в фоне)
* автоматическая конвертация картинок в avif (на лету или в фоне)
* в режиме lazyConvert не создаются дубли картинок
* не использует сторонние сервисы
* не использует curl
* почти не нагружает сервер
* оптимизирует без грязи
* освободится значительное кол-во места на диске (в 5-10 раз!)
* progressive jpeg
* возможность оптимизации через консоль (в фоне)
* гибкие настройки
* вкл/выкл автоматическое уменьшение размера картинок. Можно задать максимальный размер и все картинки которые больше будут автоматически уменьшаться до этого размера.
* учитывает ориентацию картинок (вертикальные остаются вертикальными, горизонатльные остаются горизонтальными)
* оптимизация картинок сохраняет оригинальные права на файлы
* оптимизация картинок сохраняет оригинальное время создание

## Поддержка оптимизации при:
* загрузке картинки превью и детальной у элементов
* загрузке картинки в свойство FILE Image у элементов
* загрузке картинки в разделы элементов
* загрузке картинки в модуль main
* ресайзе картинки (в т.ч. кэшируемой)

## Что также идет:
* вкл/выкл оптимизация у элементов/разделов/ресайза
* можно установить качество jpeg-файлов при сжатии
* можно установить степень сжатия у png-файлов
* вкл/выкл progressive jpeg
* можно сжать все имеющиеся картинки прямо из админки
* настройки для конвертации в webp
* агенты
  * агент конвертации картинок
  * агент автоматической очистки истекшего кэша
* выбор браузеров в которых выводятся webp/avif версии картинок
* вкл/выкл проверки поддержки браузером webp/avif через заголовки

## Консольный скрипт:
* `/bitrix/modules/dev2fun.imagecompress/console/optimize.php` - cron/cli скрипт оптимизации картинок
* `/bitrix/modules/dev2fun.imagecompress/console/convert.php` - cron/cli скрипт для конвертации картинок (из бд)
* `/bitrix/modules/dev2fun.imagecompress/console/cache-delayed-delete.php` - cron/cli скрипт по авто-очистке истекшего кэша

## ROADMAP
* ~~0.5.x: будет добавлена конвертация картинок в webp~~
* ~~0.6.x добавлен режим пост-конвертации~~
* ~~0.8.x: будет добавлен режим lazyConvert~~
* ~~0.11.x: смена расположения webp/avif версий и оптимизация~~
* 0.12.x: 
  * будет переработана оптимизация картинок через UI
  * будет улучшена первоначальная конвертация картинок
  * будет добавлена возможность автоматически удалять неиспользуемые картинки (оптимизация места на жестком диске) 
* 0.13.x: будет добавлена возможность замены дублирующих картинок на символические ссылки (значительно освободит место на HDD)
* 0.14.x: будет добавлена возможность выбора кастомного сервера оптимизации/конвертации + код для микросервиса, который вы сможете разместить в нужном месте.
* 1.0.x: будет добавлена возможность оптимизаций и конвертаций по кастомным путям

## Как установить
*Рекомендуемая установка через маркетплейс битрикса. Ниже будет описана установка, через github*

### Шаг1. Подготовка сервера
*Лучше всего это доверить опытному программисту или системному администратору*

#### Для оптимизации картинок
* Установить jpegoptim для оптимизации jpeg-картинок
* Установить optipng для оптимизации png-картинок
* Установить gs для оптимизации pdf-документов
* Установить svgo для оптимизации svg-картинок
* Установить gifsicle для оптимизации gif-картинок
* Убедиться в установке библиотеки GD
* Убедиться в доступности функции exec (используется для вызова утилит) и доступности всех установленных утилит из-под php.

#### Для конвертации картинок
* Установить cwebp для конвертации в webp-картинки
* Убедиться в установке библиотеки GD с поддержкой webp и avif (если нужен)
* Убедиться в доступности функции exec (используется только для cwebp)

### Шаг2. Установка модуля
1. Клонируете/скачиваете репозиторий к себе
1. Выбираете нужную кодировку и копируете оттуда папку dev2fun.imagecompress
1. Кладете папку dev2fun.imagecompress в /bitrix/modules/
1. Переходите на страницу `Marketplace->Установленные решения`, находите модуль в списке и нажимаете установить
1. Переходите на страницу настройки модуля
1. Активируете нужные компоненты и указываете пути из шага1. Сохраняетесь
1. Используете.

### Шаг3. Настройка модуля
* Перейти в раздел настроек модуля и активировать нужные функции

### Миграции
> *Если вы только устанавливаете модуль, то миграции применять __НЕ НАДО__!*
> эти миграции служат для перехода с версии на версию, без необходимости переустановки модуля

#### Шаги миграции
1. делаете бэкап
1. запоминаете, какая у вас версия модуля
1. копируете и заменяете файлы модуля (обязательно в нужной кодировке!)
1. копируете миграцию с вашей версии на новую в любое доступное место (например в корень сайта) и вызываете через браузер
1. при успехе вы увидите "x.x.x - Success", где x.x.x - применяемая версия

## Поддерживаемые события

|  название события | передаваемые переменные  | описание |
|---|---|---| 
| OnBeforeResizeImage | $intFileID - идентификатор файла  | Событие запускается перед началом оптимизации (до поиска файла в базе) |
| OnBeforeResizeImageJpegoptim | &$strFilePath - путь до файла,<br> &$quality - качество картинки,<br> &$params - дополнительные параметры  | Событие запускается перед началом оптимизации jpeg-картинок |
| OnBeforeResizeImageOptipng | &$strFilePath - путь до файла,<br> &$quality - степень сжатия картинки,<br> &$params - дополнительные параметры  | Событие запускается перед началом оптимизации png-картинок |
| OnAfterResizeImage | &$strFilePath - путь до файла | Событие запускается после оптимизации |
| OnBeforeCheckWebpBrowserSupport | &$supportBrowsers - массив поддерживаемых браузеров | Событие запускается перед проверкой браузера |
| OnAfterCheckWebpSupport | $result - результат проверки на поддержку webp,<br> обязательно сделайте `return $result;` | Событие запускается после всех проверок на поддержку webp |
| OnBeforePostConvertImage | &$arFiles - список файлов подлежащих конвертации (только в режиме postConvert)<br>Кэшируется | Событие запускается в режиме postConvert перед началом конвертации |
| OnBeforePostConvertReplaceImage | &$arFileReplace - список файлов подлежащих замене (только в режиме postConvert)<br>Не кэшируется | Событие запускается в режиме postConvert перед началом замены текущих картинок на webp |
| OnBeforePostConvertImage | &$file - путь до файла перед началом его конвертации | Событие запускается в режиме postConvert перед началом конвертации файла в webp |

## Donate

|                   |                                                                    |
|-------------------|--------------------------------------------------------------------|
| Bank Card | [Visa/Mastercard/Mir/Other](https://www.tinkoff.ru/cf/36wVfnMf7mo) |
| YooMoney          | [410011413398643](https://yoomoney.ru/to/410011413398643)                                                |
| Webmoney WMR (rub) | R218843696478                                                      |
| Webmoney WMU (uah) | U135571355496                                                      |
| Webmoney WMZ (usd) | Z418373807413                                                      |
| Webmoney WME (eur) | E331660539346                                                      |
| Webmoney WMX (btc) | X740165207511                                                      |
| Webmoney WML (ltc) | L718094223715                                                      |
| Webmoney WMH (bch) | H526457512792                                                      |
| PayPal            | [@darkfriend](https://www.paypal.me/darkfriend)                    |
| Payeer            | P93175651                                                          |
| Bitcoin           | 15Veahdvoqg3AFx3FvvKL4KEfZb6xZiM6n                                 |
| Litecoin          | LRN5cssgwrGWMnQruumfV2V7wySoRu7A5t                                 |
| Ethereum          | 0xe287Ac7150a087e582ab223532928a89c7A7E7B2                         |
| BitcoinCash       | bitcoincash:qrl8p6jxgpkeupmvyukg6mnkeafs9fl5dszft9fw9w             |
