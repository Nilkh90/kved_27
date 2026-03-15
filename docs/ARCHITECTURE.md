# ARCHITECTURE.md
> Детальная архитектура проекта на Laravel 11 + Livewire 3.

---

## Структура папок

```
nace-ua/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php         # Главная страница
│   │   │   ├── CatalogController.php      # Дерево каталога
│   │   │   ├── CodeController.php         # Детальная страница кода
│   │   │   ├── InfoController.php         # Методология / FAQ
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── SearchController.php   # GET /api/v1/search
│   │   │           ├── CodeController.php     # GET /api/v1/code/{id}
│   │   │           └── MappingController.php  # GET /api/v1/mapping/{kvedId}
│   │   └── Middleware/
│   │       └── ApiThrottleMiddleware.php   # Rate limiting для публичного API
│   │
│   ├── Livewire/                           # Все интерактивные компоненты
│   │   ├── SearchBar.php                  # Строка поиска с debounce
│   │   ├── ClassifierTree.php             # Раскрывающееся дерево кодов
│   │   ├── PopularChanges.php             # Таблица топ-10 изменений
│   │   └── Admin/
│   │       ├── ImportForm.php             # CSV/Excel загрузка
│   │       └── DataTable.php             # Таблица записей с редактированием
│   │
│   ├── Models/
│   │   ├── Kved2010.php                   # Модель старого классификатора
│   │   ├── Nace2027.php                   # Модель нового классификатора
│   │   ├── TransitionMapping.php          # Модель таблицы переходов
│   │   └── Tag.php                        # Синонимы для поиска
│   │
│   ├── Services/
│   │   ├── SearchService.php              # Полнотекстовый поиск (tsvector)
│   │   ├── MappingService.php             # Логика 1_TO_1 / 1_TO_N / N_TO_1
│   │   ├── ClassifierService.php          # Работа с иерархическим деревом
│   │   ├── ImportService.php              # Парсинг и валидация CSV/Excel
│   │   └── AiService.php                  # OpenAI интеграция (фаза 2)
│   │
│   ├── Jobs/
│   │   └── ProcessCsvImport.php           # Фоновый импорт больших файлов
│   │
│   └── Enums/
│       ├── TransitionType.php             # 1_TO_1, 1_TO_N, N_TO_1
│       └── ClassifierLevel.php            # SECTION, DIVISION, GROUP, CLASS
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php             # Основной layout: шапка, футер
│   │   │   └── admin.blade.php           # Layout для админ-панели
│   │   ├── pages/
│   │   │   ├── home.blade.php            # Главная страница
│   │   │   ├── catalog.blade.php         # Каталог классификатора
│   │   │   ├── code-detail.blade.php     # Детальная страница кода
│   │   │   └── info.blade.php            # Методология / FAQ
│   │   ├── components/
│   │   │   ├── status-badge.blade.php    # 🟢🟡🔴 Индикатор статуса
│   │   │   ├── code-card.blade.php       # Карточка кода в результатах
│   │   │   ├── mapping-panel.blade.php   # Блок сравнения «было/стало»
│   │   │   ├── includes-excludes.blade.php # Блоки «Включает» и «Исключает»
│   │   │   └── breadcrumbs.blade.php     # Хлебные крошки для SEO
│   │   ├── livewire/
│   │   │   ├── search-bar.blade.php      # Шаблон строки поиска
│   │   │   ├── classifier-tree.blade.php # Шаблон дерева
│   │   │   ├── popular-changes.blade.php # Шаблон таблицы изменений
│   │   │   └── admin/
│   │   │       ├── import-form.blade.php
│   │   │       └── data-table.blade.php
│   │   └── admin/
│   │       └── dashboard.blade.php       # Дашборд статистики
│   │
│   └── css/
│       └── app.css                       # Tailwind + CSS-переменные
│
├── database/
│   ├── migrations/
│   │   ├── create_kved_2010_table.php
│   │   ├── create_nace_2027_table.php
│   │   ├── create_transition_mapping_table.php
│   │   └── create_tags_table.php
│   └── seeders/
│       └── DatabaseSeeder.php            # Тестовые данные для разработки
│
├── routes/
│   ├── web.php                           # Веб-маршруты
│   └── api.php                           # API v1 маршруты
│
└── docs/                                 # ← ВЫ ЗДЕСЬ
    ├── PROJECT_OVERVIEW.md
    ├── ARCHITECTURE.md
    ├── TECH_STACK.md
    └── CURRENT_STATUS.md
```

---

## Компоненты: детальное описание

### Livewire: SearchBar.php
**Ответственность**: Реактивная строка поиска с debounce 300ms. Отправляет запрос к `SearchService` при каждом изменении ввода, обновляет список подсказок без перезагрузки страницы.

```php
class SearchBar extends Component
{
    #[Url]
    public string $query = '';
    public array $results = [];

    #[On('search-updated')]
    public function updatedQuery(): void
    {
        $this->results = app(SearchService::class)->suggest($this->query, limit: 8);
    }
}
```

**Особенности**: При выборе результата выполняет `redirect()->route('code.show', $code)`. Кнопка «AI-поиск» открывает модал (Alpine.js `x-show`).

---

### Livewire: ClassifierTree.php
**Ответственность**: Рекурсивное дерево классификатора с ленивой загрузкой дочерних узлов. Каждый клик на раздел загружает его детей через Livewire-запрос (без полной перезагрузки страницы).

```php
class ClassifierTree extends Component
{
    public string $standard = 'kved'; // 'kved' или 'nace'
    public array $expanded = [];

    public function toggle(string $id): void
    {
        if (in_array($id, $this->expanded)) {
            $this->expanded = array_diff($this->expanded, [$id]);
        } else {
            $this->expanded[] = $id;
        }
    }
}
```

**Особенности**: Состояние `$expanded` хранится в Livewire-сессии. Анимация раскрытия — чистый CSS transition (`max-height: 0 → auto`). URL обновляется через `#[Url]` при выборе раздела — SEO-friendly.

---

### Blade-компонент: status-badge.blade.php
**Ответственность**: Единственный источник правды для цветового статуса перехода. Используется на всех страницах где есть маппинг.

```blade
@props(['type', 'actionRequired' => false])

@php
$config = match($type) {
    '1_TO_1' => ['color' => 'green',  'label' => 'Автоматический переход'],
    '1_TO_N' => ['color' => 'amber',  'label' => 'Нужен выбор направления'],
    'N_TO_1' => ['color' => 'blue',   'label' => 'Коды объединены'],
};
if ($actionRequired) $config = ['color' => 'red', 'label' => 'Нужна перерегистрация'];
@endphp

<span class="badge badge-{{ $config['color'] }}">{{ $config['label'] }}</span>
```

---

### Service: SearchService.php
**Ответственность**: Весь полнотекстовый поиск. Принимает строку запроса, ищет одновременно в `kved_2010`, `nace_2027` и `tags`, возвращает ранжированный список.

```php
class SearchService
{
    public function search(string $query): Collection
    {
        $tsQuery = "plainto_tsquery('ukrainian', ?)";

        return DB::select("
            SELECT id, code, title, 'kved' as standard,
                   ts_rank(search_vector, {$tsQuery}) as rank
            FROM kved_2010
            WHERE search_vector @@ {$tsQuery}
            UNION ALL
            SELECT id, code, title, 'nace' as standard,
                   ts_rank(search_vector, {$tsQuery}) as rank
            FROM nace_2027
            WHERE search_vector @@ {$tsQuery}
            ORDER BY rank DESC
            LIMIT 20
        ", [$query, $query, $query, $query]);
    }
}
```

---

### Service: MappingService.php
**Ответственность**: Логика получения маппинга для конкретного кода. Возвращает структурированный объект с типом перехода, комментарием и списком связанных кодов.

---

### Service: ImportService.php
**Ответственность**: Парсинг и валидация CSV/Excel файлов в админ-панели. Поддерживает режимы `upsert` (обновить существующие + добавить новые) и `replace` (полная замена). Для файлов > 1000 строк — диспетчирует `ProcessCsvImport` job в очередь.

---

## Схема базы данных

### kved_2010
```sql
CREATE TABLE kved_2010 (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code          VARCHAR(10) NOT NULL UNIQUE,
    title         VARCHAR(500) NOT NULL,
    level         VARCHAR(20) NOT NULL CHECK (level IN ('SECTION','DIVISION','GROUP','CLASS','SUBCLASS')),
    parent_id     UUID REFERENCES kved_2010(id),
    description   TEXT,
    includes      JSONB,
    excludes      JSONB,
    search_vector TSVECTOR,
    created_at    TIMESTAMPTZ DEFAULT now(),
    updated_at    TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_kved_parent   ON kved_2010(parent_id);
CREATE INDEX idx_kved_code     ON kved_2010(code);
CREATE INDEX idx_kved_fts      ON kved_2010 USING GIN(search_vector);
```

### nace_2027
```sql
-- Структура идентична kved_2010
CREATE TABLE nace_2027 (
    id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code          VARCHAR(10) NOT NULL UNIQUE,
    title         VARCHAR(500) NOT NULL,
    level         VARCHAR(20) NOT NULL,
    parent_id     UUID REFERENCES nace_2027(id),
    description   TEXT,
    includes      JSONB,
    excludes      JSONB,
    search_vector TSVECTOR,
    created_at    TIMESTAMPTZ DEFAULT now(),
    updated_at    TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_nace_parent ON nace_2027(parent_id);
CREATE INDEX idx_nace_code   ON nace_2027(code);
CREATE INDEX idx_nace_fts    ON nace_2027 USING GIN(search_vector);
```

### transition_mapping
```sql
CREATE TABLE transition_mapping (
    id                 UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    old_kved_id        UUID NOT NULL REFERENCES kved_2010(id),
    new_nace_id        UUID NOT NULL REFERENCES nace_2027(id),
    transition_type    VARCHAR(10) NOT NULL CHECK (transition_type IN ('1_TO_1','1_TO_N','N_TO_1')),
    action_required    BOOLEAN NOT NULL DEFAULT false,
    transition_comment TEXT,
    view_count         INTEGER DEFAULT 0,  -- для сортировки PopularChanges
    created_at         TIMESTAMPTZ DEFAULT now()
);
CREATE INDEX idx_mapping_kved ON transition_mapping(old_kved_id);
CREATE INDEX idx_mapping_nace ON transition_mapping(new_nace_id);
```

### tags
```sql
CREATE TABLE tags (
    id       UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nace_id  UUID NOT NULL REFERENCES nace_2027(id),
    tag      VARCHAR(200) NOT NULL,
    lang     VARCHAR(5) DEFAULT 'uk'
);
CREATE INDEX idx_tags_fts ON tags USING GIN(to_tsvector('ukrainian', tag));
```

---

## Маршруты (routes/web.php)

```php
// Публичные
Route::get('/',                          [HomeController::class, 'index'])->name('home');
Route::get('/catalog',                   [CatalogController::class, 'index'])->name('catalog');
Route::get('/catalog/{standard}',        [CatalogController::class, 'byStandard']); // kved|nace
Route::get('/code/{standard}/{code}',    [CodeController::class, 'show'])->name('code.show');
Route::get('/info',                      [InfoController::class, 'index'])->name('info');
Route::get('/info/{slug}',               [InfoController::class, 'article'])->name('info.article');

// Админ (basic auth middleware)
Route::middleware('auth.admin')->prefix('admin')->group(function () {
    Route::get('/',        [AdminController::class, 'dashboard']);
    Route::get('/import',  [AdminController::class, 'import']);
});
```

```php
// routes/api.php — публичное API (фаза 2)
Route::middleware('throttle:api')->prefix('v1')->group(function () {
    Route::get('/search',          [Api\V1\SearchController::class, 'index']);
    Route::get('/code/{id}',       [Api\V1\CodeController::class, 'show']);
    Route::get('/mapping/{kvedId}',[Api\V1\MappingController::class, 'show']);
});
```

---

## SEO-стратегия

### Кеширование страниц через spatie/laravel-responsecache
```php
// Заменяет ISR из Next.js. Страницы кодов кешируются на 24 часа.
// После импорта новых данных — ResponseCache::clear() в ImportService.

Route::get('/code/{standard}/{code}', [CodeController::class, 'show'])
    ->middleware('cacheResponse:1440'); // 1440 минут = 24 часа
```

### Динамический sitemap
```php
// routes/web.php
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// SitemapController — генерирует XML из БД, кешируется на 24ч через Redis
```

### Мета-теги через blade-компоненты
```blade
{{-- В code-detail.blade.php --}}
<x-seo
    :title="$code->code . ' — ' . $code->title . ' | NACE 2.1-UA'"
    :description="'Код ' . $code->code . ': что включает, статус перехода на NACE 2027. ' . Str::limit($code->description, 120)"
    :canonical="route('code.show', [$standard, $code->code])"
/>
```
