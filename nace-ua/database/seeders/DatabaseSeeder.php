<?php

namespace Database\Seeders;

use App\Models\Kved2010;
use App\Models\Nace2027;
use App\Models\TransitionMapping;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Очистка таблиц в правильном порядке (учитываем внешние ключи).
        TransitionMapping::truncate();
        Tag::truncate();
        Nace2027::truncate();
        Kved2010::truncate();

        // Базовые тестовые данные для отладки поиска и маппингов.

        $kved62 = Kved2010::create([
            'code' => '62',
            'title' => 'Комп’ютерне програмування, консультування та пов’язані послуги',
            'level' => 'DIVISION',
        ]);

        $kved6201 = Kved2010::create([
            'code' => '62.01',
            'title' => 'Комп’ютерне програмування',
            'level' => 'CLASS',
            'parent_id' => $kved62->id,
        ]);

        $kved6202 = Kved2010::create([
            'code' => '62.02',
            'title' => 'Консультування з питань інформатизації',
            'level' => 'CLASS',
            'parent_id' => $kved62->id,
        ]);

        $kved6203 = Kved2010::create([
            'code' => '62.03',
            'title' => 'Управління комп’ютерним устаткованням',
            'level' => 'CLASS',
            'parent_id' => $kved62->id,
        ]);

        $nace62 = Nace2027::create([
            'code' => '62',
            'title' => 'Комп’ютерне програмування, консультування та пов’язані послуги (NACE 2.1-UA)',
            'level' => 'DIVISION',
        ]);

        $nace6201 = Nace2027::create([
            'code' => '62.01',
            'title' => 'Розроблення програмного забезпечення',
            'level' => 'CLASS',
            'parent_id' => $nace62->id,
        ]);

        $nace6202 = Nace2027::create([
            'code' => '62.02',
            'title' => 'Консультування у сфері інформаційних технологій',
            'level' => 'CLASS',
            'parent_id' => $nace62->id,
        ]);

        $nace6203 = Nace2027::create([
            'code' => '62.03',
            'title' => 'Управління комп’ютерним устаткованням (аутсорсинг ІТ-інфраструктури)',
            'level' => 'CLASS',
            'parent_id' => $nace62->id,
        ]);

        // Простейшие примеры маппингов: 1_TO_1 и 1_TO_N.
        TransitionMapping::create([
            'old_kved_id' => $kved6201->id,
            'new_nace_id' => $nace6201->id,
            'transition_type' => '1_TO_1',
            'action_required' => false,
            'transition_comment' => 'Прямий перехід для продуктових/аутсорс-компаній.',
            'view_count' => 15,
        ]);

        TransitionMapping::create([
            'old_kved_id' => $kved6202->id,
            'new_nace_id' => $nace6202->id,
            'transition_type' => '1_TO_1',
            'action_required' => false,
            'transition_comment' => 'Класичний ІТ-консалтинг, без суттєвих змін опису.',
            'view_count' => 8,
        ]);

        TransitionMapping::create([
            'old_kved_id' => $kved6203->id,
            'new_nace_id' => $nace6203->id,
            'transition_type' => '1_TO_1',
            'action_required' => true,
            'transition_comment' => 'Потрібно перевірити договори на відповідність новим формулюванням.',
            'view_count' => 4,
        ]);

        // Дополнительные маппинги для демонстрации 1_TO_N / N_TO_1.
        foreach (range(1, 7) as $i) {
            TransitionMapping::create([
                'old_kved_id' => $kved62->id,
                'new_nace_id' => [$nace6201, $nace6202, $nace6203][$i % 3]->id,
                'transition_type' => $i % 2 === 0 ? '1_TO_N' : 'N_TO_1',
                'action_required' => $i % 3 === 0,
                'transition_comment' => 'Тестовий маппінг #' . $i . ' для демонстрації таблиці переходів.',
                'view_count' => $i * 3,
            ]);
        }

        // Теги-синоніми для пошуку.
        $tags = [
            'аутсорс розробка',
            'IT консалтинг',
            'супровід ПЗ',
            'розробка мобільних застосунків',
            'аутстаф розробників',
            'керування серверами',
            'DevOps-послуги',
            'розробка SaaS',
            'аутсорс інфраструктури',
            'підтримка користувачів',
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'nace_id' => $nace6201->id,
                'tag' => $tag,
                'lang' => 'uk',
            ]);
        }
    }
}
