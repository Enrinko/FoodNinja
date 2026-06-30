# Short Links — сервис коротких ссылок

Веб-приложение на **Laravel 11**: пользователи создают короткие ссылки,
переходы по ним редиректят на оригинальный URL и фиксируются в статистике,
а управление и аналитика доступны в личном кабинете на **Filament v3**.

---

## Возможности (соответствие ТЗ)

| Требование | Реализация |
|------------|------------|
| Регистрация и вход | Встроенная аутентификация Filament: `/admin/login`, `/admin/register` |
| Создание короткой ссылки | Указывается `Original URL` → автогенерация `short_code` (base62, 6 симв.) |
| Перенаправление | `GET /{code}` → 302 на оригинальный URL |
| Фиксация перехода | На каждый переход пишется `IP`, `дата/время`, `User-Agent`, `Referer` |
| Список своих ссылок | Таблица в кабинете с коротким URL и счётчиком кликов |
| Удаление ссылки | Действие Delete (каскадно удаляет клики) |
| Статистика по ссылке | Страница View: список переходов (IP, время, UA, Referer) + общее число кликов |
| Изоляция пользователей | `LinkResource::getEloquentQuery()` + `LinkPolicy` — каждый видит только свои ссылки |
| Filament v3 (бонус) | Личный кабинет полностью на Filament v3 + виджет «Моих ссылок / Всего кликов» |

## Технологии

- **Laravel 11**, **PHP 8.3**
- **Filament v3** — личный кабинет / админ-панель
- **PostgreSQL 16**
- **Laravel Sail** (Docker)
- Тесты — **PHPUnit** (sqlite `:memory:`)

---

## Требования

- **Docker Desktop** (на Windows — с включённым WSL2)
- **Git**

Локальные PHP/Composer **не нужны** — всё ставится через Docker.

## Быстрый старт (Docker, из GitHub)

```bash
git clone https://github.com/Enrinko/FoodNinja.git
cd FoodNinja
cp .env.example .env

# 1) Установить зависимости (vendor/). Лёгкий composer-образ не содержит ext-intl,
#    поэтому при бутстрапе добавляем --ignore-platform-reqs (в рантайме intl есть).
docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html \
  laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# 2) Поднять стек: приложение (:8080) + PostgreSQL
docker compose up -d --build

# 3) Ключ приложения, миграции и демо-данные
docker compose exec -u sail laravel.test php artisan key:generate
docker compose exec -u sail laravel.test php artisan migrate --seed

# 4) Гарантировать запись логов/кэша (для dev на bind-mount)
docker compose exec -u root laravel.test chmod -R 777 storage bootstrap/cache
```

Откройте **http://localhost:8080** → редирект на `/admin`.

> Windows: команды выше работают как в **PowerShell**, так и в **Git Bash**.
> `${PWD}` подставит текущий путь автоматически.

### Демо-доступ

После `--seed` создаётся демо-пользователь:

- **email:** `demo@example.com`
- **пароль:** `password`

Либо зарегистрируйте нового на `/admin/register`.

---

## Как пользоваться

1. Войдите (`/admin/login`) или зарегистрируйтесь (`/admin/register`).
2. **Links → New link** → укажите `Original URL` (напр. `https://example.com/page`).
   Короткий код сгенерируется автоматически.
3. В списке ссылок — короткий URL (кнопка копирования) и счётчик кликов.
4. Откройте короткий URL (`http://localhost:8080/{code}`) → редирект на оригинал,
   переход попадёт в статистику.
5. Действие **View** у ссылки → список переходов (IP, дата/время, User-Agent,
   Referer) и общее число кликов. На дашборде — суммарные «Моих ссылок / Всего кликов».

## Тесты

```bash
docker compose exec -u sail laravel.test php artisan test
```

Покрыто: редирект на оригинал, запись клика (IP/время), 404 на неизвестный код,
генерация уникального кода, изоляция ссылок между пользователями.

## Альтернатива: Laravel Sail (macOS / Linux / WSL2)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan test
```

---

## Архитектура (ключевые файлы)

```
app/
  Http/Controllers/RedirectController.php   # поиск по коду, запись клика, redirect away
  Services/ShortCodeGenerator.php           # уникальный base62-код
  Models/{Link,Click,User}.php              # связи hasMany/belongsTo
  Policies/LinkPolicy.php                   # доступ только владельцу
  Filament/Resources/LinkResource.php       # форма/таблица/infolist + scope по user_id
  Filament/Resources/LinkResource/RelationManagers/ClicksRelationManager.php  # статистика (read-only)
  Filament/Widgets/LinkStatsOverview.php    # дашборд: Моих ссылок / Всего кликов
database/migrations/                        # links, clicks
routes/web.php                              # '/' -> /admin; '/{code}' -> RedirectController
```

- **Маршрут редиректа** объявлен последним и ограничен `[A-Za-z0-9]{6}`, чтобы не
  перехватывать `/admin`, ассеты Filament и прочие маршруты.
- **Короткий код** генерируется в событии `Link::creating` (одинаково из панели,
  сидера и тестов); уникальность гарантируется проверкой + unique-индексом.
- **Изоляция**: `getEloquentQuery()` фильтрует по `auth()->id()`, `user_id`
  проставляется автоматически при создании, плюс `LinkPolicy` как защита «в глубину».

## Замечания

- Приложение слушает порт **8080** (`APP_PORT` в `.env`), PostgreSQL проброшен на 5432.
- Запись клика синхронная — для масштаба задачи достаточно; при высокой нагрузке
  выносится в очередь.
- Для команд `artisan`/`composer` внутри контейнера используйте `-u sail`, чтобы
  файлы создавались под тем же пользователем, что и веб-процесс (иначе возможны
  ошибки прав на `storage/`).
