<?php

namespace App\Services;

class ClassifierService
{
    /**
     * Временная заглушка: возвращает небольшое дерево разделов.
     *
     * @return array<int, array{id:string,code:string,title:string,children:array<int,array{id:string,code:string,title:string}>}>
     */
    public function getRootNodes(string $standard): array
    {
        return [
            [
                'id' => 'A',
                'code' => 'A',
                'title' => 'Сільське, лісове та рибне господарство',
                'children' => [
                    ['id' => '01', 'code' => '01', 'title' => 'Рослинництво і тваринництво'],
                    ['id' => '02', 'code' => '02', 'title' => 'Лісове господарство та лісозаготівлі'],
                ],
            ],
            [
                'id' => 'J',
                'code' => 'J',
                'title' => 'Інформація та телекомунікації',
                'children' => [
                    ['id' => '62', 'code' => '62', 'title' => 'Комп\'ютерне програмування, консультування та пов\'язані послуги'],
                    ['id' => '63', 'code' => '63', 'title' => 'Інформаційні послуги'],
                ],
            ],
        ];
    }
}

