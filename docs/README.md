# NACE 2.1-UA (kved_27)

Cервіс для швидкого та зручного переходу українського бізнесу з класифікатора КВЕД-2010 на новий стандарт NACE 2.1-UA (який вступає в силу у 2027 році згідно з Наказом Держстату № 191).

## Опис проекту
Проект вирішує проблему невизначеності бізнесу під час зміни класифікатора. Він дозволяє за лічені секунди знайти відповідність між старим та новим кодами, оцінити юридичні наслідки (необхідність перереєстрації) та ознайомитися з детальною структурою обох стандартів.

## Технологічний стек
- **Backend**: [PHP 8.3+](https://www.php.net/), [Laravel 11+](https://laravel.com/)
- **Frontend**: [Livewire 3](https://livewire.laravel.com/), [Alpine.js 3](https://alpinejs.dev/), [Tailwind CSS 4](https://tailwindcss.com/)
- **Database**: [PostgreSQL 15+](https://www.postgresql.org/) (використовує GIN-індекси та `tsvector` для повнотекстового пошуку)
- **Cache & Queues**: [Redis 7+](https://redis.io/)
- **Infrastructure**: Nginx, PHP-FPM, Supervisor (для воркерів черг)

## Швидкий старт (Getting Started)

### 1. Клонування репозиторію
```bash
git clone <repository_url>
cd kved_27/nace-ua
```

### 2. Встановлення залежностей
```bash
composer install
npm install
```

### 3. Налаштування середовища
```bash
cp .env.example .env
php artisan key:generate
```
Відредагуйте `.env` та вкажіть параметри підключення до вашої бази даних (PostgreSQL) та Redis.

### 4. Міграції та початкові дані
```bash
# Створення таблиць та наповнення базовими даними
php artisan migrate --seed

# (Опціонально) Імпорт актуального КВЕД-2010 з сайту Держстату
php artisan kved:import
```

### 5. Запуск проекту
```bash
# Запуск локального сервера розробки
php artisan serve

# Компіляція фронтенд-активів (Vite)
npm run dev

# Запуск обробки черг (необхідно для імпорту великих CSV-файлів)
php artisan queue:work
```

## Структура проекту
Основна логіка зосереджена в директорії `nace-ua/`:
- `app/Http/Controllers` — контролери публічних сторінок та API.
- `app/Livewire` — реактивні UI компоненти: `SearchBar` (пошук), `ClassifierTree` (каталог), `Admin\ImportForm`.
- `app/Models` — Eloquent моделі: `Kved2010`, `Nace2027`, `TransitionMapping` (таблиця зв'язків).
- `app/Services` — сервісний шар: `SearchService`, `MappingService`, `ImportService`.
- `app/Jobs` — фонові завдання для імпорту важких файлів.
- `resources/views` — Blade-шаблони, згруповані за сторінками та компонентами.
- `database/migrations` — опис схеми БД PostgreSQL.

## Використання
- **Пошук**: Використовуйте розумний пошук на головній сторінці для швидкого пошуку по коду або опису (підтримує обидва стандарти).
- **Каталог**: Переглядайте повну ієрархію класифікаторів у розділі [Каталог](http://localhost:8000/catalog).
- **Адмін-панель**: Доступна за адресою `/admin` (захищена Basic Auth). Дозволяє переглядати статистику та завантажувати нові дані через CSV/Excel.
- **Тести**: Запуск юніт та feature тестів: `php artisan test`.

---
*Senior Tech Lead & Technical Writer*
