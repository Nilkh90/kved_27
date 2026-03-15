# TECH_STACK.md
> Технологический стек, обоснование выбора, версии и ключевые пакеты.

---

## Основной стек

| Технология | Версия | Роль | Обоснование |
|---|---|---|---|
| **PHP** | 8.3+ | Язык | Нативная экосистема, большой рынок разработчиков |
| **Laravel** | 11+ | Фреймворк | ORM, очереди, кеш, API-токены, маршруты — всё из коробки |
| **Livewire** | 3+ | Реактивный UI | Интерактивный поиск и дерево без написания отдельного JS-фреймворка |
| **Alpine.js** | 3+ | Лёгкие JS-взаимодействия | Модалы, анимации, toggle — идёт в комплекте с Livewire |
| **Blade** | — | Шаблонизатор | Рендеринг на сервере → полноценный SEO |
| **Tailwind CSS** | 3+ | Стили | Утилиты для адаптивности, Mobile First из коробки |
| **PostgreSQL** | 15+ | База данных | tsvector, JSONB, UUID, GIN-индексы — всё что нужно |
| **Redis** | 7+ | Кеш + очереди | Кеш страниц, кеш дерева классификатора, очереди импорта |

---

## Ключевые пакеты Composer

| Пакет | Роль |
|---|---|
| `livewire/livewire` | Реактивные компоненты без SPA |
| `spatie/laravel-responsecache` | Кеширование HTML-страниц (замена ISR) |
| `spatie/laravel-sitemap` | Динамический sitemap.xml из БД |
| `spatie/laravel-permission` | Роли/права для админ-панели (фаза 2) |
| `league/csv` | Парсинг CSV для импорта |
| `phpoffice/phpspreadsheet` | Парсинг Excel (.xlsx) от Госстата |
| `openai-php/laravel` | AI-помощник (фаза 2) |
| `laravel/sanctum` | API-токены для платного API (фаза 2) |
| `predis/predis` | Redis клиент для PHP |

---

## Цветовая палитра (CSS переменные)

```css
:root {
  /* --- Основные --- */
  --color-primary:       #1A5FBE;  /* Синий — доверие */
  --color-primary-dark:  #0F4494;  /* Hover */
  --color-primary-light: #EEF4FF;  /* Фоны, подсветка */

  /* --- Нейтральные --- */
  --color-bg:            #FFFFFF;
  --color-surface:       #F8F9FC;
  --color-border:        #E2E8F2;
  --color-text:          #0F1923;
  --color-text-muted:    #5A6A7F;
  --color-text-hint:     #94A3B8;

  /* --- Статусы перехода --- */
  --color-success:       #16A34A;  /* 1_TO_1: автоматический */
  --color-success-bg:    #DCFCE7;
  --color-warning:       #D97706;  /* 1_TO_N / N_TO_1: нужен выбор */
  --color-warning-bg:    #FEF3C7;
  --color-danger:        #DC2626;  /* action_required: нужна регистрация */
  --color-danger-bg:     #FEE2E2;
  --color-info:          #0284C7;  /* Информационные блоки */
  --color-info-bg:       #E0F2FE;
}
```

### Применение по компонентам
- `--color-primary` → CTA-кнопки, активные ссылки, курсор поиска
- `--color-success*` → `status-badge` «Автоматический переход», зелёные маркеры в маппинге
- `--color-warning*` → `status-badge` «Нужен выбор», жёлтые иконки в PopularChanges
- `--color-danger*` → `status-badge` «Нужна регистрация», красные предупреждения
- `--color-surface` → фон карточек кодов, фон каталога

---

## Инфраструктура

```
Любой VPS (Ubuntu 22.04) или shared hosting с PHP 8.3
├── Nginx          → статические файлы + proxy к PHP-FPM
├── PHP-FPM 8.3    → Laravel приложение
├── PostgreSQL 15  → основная БД (на том же сервере или отдельный)
├── Redis 7        → кеш страниц + очереди импорта
└── Supervisor     → запуск Laravel queue worker (для импорта CSV)
```

**Рекомендуемые хостинги:**
- Timeweb Cloud VPS — от 300 грн/мес, Украина
- DigitalOcean Droplet $6/мес — если нужен международный CDN
- ukraine.com.ua shared — самый дешёвый старт (но без очередей)

---

## Типографика

```css
/* Основной — читаемый, нейтральный */
font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;

/* Моноширный для кодов: 62.01, J62.010 */
font-family: 'JetBrains Mono', 'Courier New', monospace;
```

**Шкала размеров:**
- Hero-заголовок: 42px / 600
- Заголовок страницы: 28px / 600
- Код (крупный, детальная страница): 26px mono / 700
- Подзаголовок секции: 18px / 600
- Тело текста: 16px / 400, line-height: 1.65
- Подпись / метаданные: 14px / 400
- Микро-подпись: 12px / 400

---

## SEO-инструменты

| Инструмент | Реализация |
|---|---|
| Мета-теги | Blade-компонент `<x-seo>`, уникальные title/description на каждой странице |
| Sitemap | `spatie/laravel-sitemap` — генерируется из БД, кешируется в Redis на 24ч |
| robots.txt | Статический файл в `public/`, закрывает `/admin` |
| JSON-LD | Blade-компонент `<x-structured-data>`, тип `ItemList` для каталога |
| Canonical | Устанавливается в `<x-seo>` для страниц с параметрами фильтров |
| Хлебные крошки | Компонент `breadcrumbs.blade.php` + JSON-LD BreadcrumbList |
