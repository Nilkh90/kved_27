# CURRENT_STATUS.md
> Живой документ. Обновлять после завершения каждого этапа.

---

## Текущий статус
**Фаза**: 🟡 ФАЗА 2 — ядро продукта (в процессе)
**Версия**: 0.2.0-alpha
**Сервер**: http://204.168.131.195 (Ubuntu 22.04 LTS, Hetzner VDS, aarch64)

Сделано на текущий момент:
- Инициализирован Laravel‑проект `nace-ua` (`composer create-project`)
- Установлены и подключены Livewire 4, Tailwind CSS 4, Alpine.js через Vite
- Реализован Livewire‑компонент `SearchBar` с debounce и тестовыми подсказками через `SearchService::suggest()`
- Реализован Livewire‑компонент `ClassifierTree` с тестовым деревом из `ClassifierService`
- Главная страница `home.blade.php` отображает строку поиска и «популярні зміни»
- **Задеплоено на VDS**: PHP 8.3, Nginx, PostgreSQL 14, Redis, Supervisor, Node.js 20
- Миграции выполнены, frontend собран (`npm run build`), Laravel-кеши оптимизированы

---

## Дорожная карта

### ФАЗА 1 — Фундамент (1–2 недели)
**Цель**: Работающий Laravel с БД, базовым поиском и одной страницей.

- [x] `composer create-project laravel/laravel nace-ua`
- [x] Установка: Livewire 4, Tailwind CSS 4, Alpine.js через Vite
- [x] Настройка PostgreSQL + `.env` (на сервере: БД `nace_ua`, user `nace_user`)
- [x] Написание миграций: `kved_2010`, `nace_2027`, `transition_mapping`, `tags`
- [x] `php artisan migrate` (пока для базовых таблиц Laravel, SQLite)
- [x] Создание моделей (`Kved2010`, `Nace2027`, `TransitionMapping`)
- [x] Seed с тестовыми записями + 10+ маппингами (локально + на сервере)
- [x] `SearchService.php` — базовый поиск (пока через in-memory заглушку, позже tsvector)
- [x] Livewire компонент `SearchBar.php` с debounce
- [x] Шаблон `search-bar.blade.php`
- [x] Главная страница `home.blade.php` со строкой поиска
- [x] Деплой на VPS (Nginx + PHP-FPM + Supervisor) — http://204.168.131.195

**Критерий готовности**: Можно ввести «62» в поиск и увидеть релевантний список кодів (на тестових даних, без tsvector).

---

### ФАЗА 2 — Ядро продукта (2–3 недели)
**Цель**: Полный цикл: поиск → деталь → маппинг → статус.

- [ ] Заменить ILIKE на полнотекстовый поиск через `tsvector` + GIN-индексы
- [ ] Наполнить `tags` синонимами для топ-100 видов бизнеса
- [x] `MappingService.php` — базовая логика 1_TO_1 / 1_TO_N / N_TO_1
- [x] Blade-компонент `status-badge.blade.php` (три варианта)
- [x] Blade-компонент `mapping-panel.blade.php` — сравнение «до/после»
- [x] Blade-компонент `includes-excludes.blade.php` — блоки с гиперссылками
- [x] Страница `code-detail.blade.php` с базовым контентом
- [x] `ClassifierService.php` + Livewire `ClassifierTree.php` (чтение з реальної БД, поки на тестових даних)
- [x] Страница каталога `catalog.blade.php` с деревом
- [x] Livewire `PopularChanges.php` — топ-10 змін (на тестових даних)
- [x] Главная: `ValueProposition`, `CTASection` (статические Blade-компоненты)
- [x] Страница методологии `info.blade.php` (базовый шаблон)
- [x] SEO: `<x-seo>` компонент (мета-теги + JSON-LD), без sitemap/robots поки що
- [ ] Кеширование через `spatie/laravel-responsecache` + Redis

**Критерий готовности**: Продукт функционально готов для показа инвесторам/партнёрам (демо-поток: пошук → деталь → маппінг → статус).

---

### ФАЗА 3 — Админ-панель и данные (1–2 недели)
**Цель**: Менеджер может обновлять БД без разработчика.

- [ ] Middleware `auth.admin` (простой basic auth через `.env`)
- [ ] Дашборд `/admin` — статистика записей в БД
- [ ] Livewire `ImportForm.php` — drag-and-drop CSV/Excel
- [ ] `ImportService.php` — парсинг, валидация, upsert
- [ ] `ProcessCsvImport` job — для файлов > 1000 строк
- [ ] Livewire `DataTable.php` — просмотр и редактирование записей
- [ ] После импорта: автоматически `ResponseCache::clear()` + пересчёт `search_vector`
- [ ] Наполнение БД полным датасетом КВЭД-2010
- [ ] Наполнение БД полным датасетом NACE 2.1-UA
- [ ] Заполнение `transition_mapping` (главная работа по данным!)

**Критерий готовности**: Менеджер может загрузить новый CSV и данные обновятся за 5 минут.

---

### ФАЗА 4 — Монетизация и AI (4+ недель)
**Цель**: Платные функции, API для B2B.

- [ ] Система авторизации (Laravel Breeze или Jetstream)
- [ ] Личный кабинет: сохранённые коды, история
- [ ] `AiService.php` + маршрут `POST /api/ai/suggest`
- [ ] Livewire-модал AI-помощника — свободный текст → код
- [ ] Laravel Sanctum — API-токены для платного доступа
- [ ] Rate limiting через `ApiThrottleMiddleware`
- [ ] Интеграция Stripe/LiqPay для подписок
- [ ] OpenAPI документация для `/api/v1`

---

## Известные риски

| Риск | Вероятность | Решение |
|---|---|---|
| Госстат обновит таблицу NACE до 2027 | Высокая | Админ-панель с импортом CSV, очистка кеша автоматически |
| Неполные данные маппинга на старте | Высокая | Показывать «маппинг уточняется» вместо ошибки |
| Livewire и большое дерево (5000+ узлов) | Средняя | Ленивая загрузка дочерних узлов, кеш дерева в Redis |
| AI-помощник галлюцинирует | Средняя | Всегда показывать ссылку на первоисточник, добавить disclaimer |

---

## Команда

| Роль | Ответственность |
|---|---|
| PHP-разработчик | Laravel, Livewire, БД, импорт |
| Frontend | Tailwind, Blade-компоненты, адаптивность |
| Контент-менеджер | Наполнение маппинга, FAQ |
