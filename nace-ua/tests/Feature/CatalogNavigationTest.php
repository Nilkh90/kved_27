<?php

namespace Tests\Feature;

use App\Models\Nace2027;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed some data
        $section = Nace2027::create([
            'code' => 'A',
            'title' => 'Agriculture',
            'level' => 'SECTION',
        ]);

        $division = Nace2027::create([
            'code' => '01',
            'title' => 'Crop production',
            'level' => 'DIVISION',
            'parent_id' => $section->id,
        ]);

        $group = Nace2027::create([
            'code' => '01.1',
            'title' => 'Growing of non-perennial crops',
            'level' => 'GROUP',
            'parent_id' => $division->id,
        ]);

        $class = Nace2027::create([
            'code' => '01.11',
            'title' => 'Growing of cereals',
            'level' => 'CLASS',
            'parent_id' => $group->id,
            'description' => 'Test class description',
        ]);
    }

    public function test_catalog_index_returns_sections()
    {
        $response = $this->get(route('catalog'));

        $response->assertStatus(200);
        $response->assertSee('Agriculture');
    }

    public function test_catalog_section_returns_divisions()
    {
        $section = Nace2027::where('level', 'SECTION')->first();
        $response = $this->get(route('catalog.section', $section->id));

        $response->assertStatus(200);
        $response->assertSee('Agriculture');
        $response->assertSee('Crop production');
    }

    public function test_catalog_division_returns_groups()
    {
        $division = Nace2027::where('level', 'DIVISION')->first();
        $response = $this->get(route('catalog.division', $division->id));

        $response->assertStatus(200);
        $response->assertSee('Crop production');
        $response->assertSee('Growing of non-perennial crops');
    }

    public function test_catalog_group_returns_classes()
    {
        $group = Nace2027::where('level', 'GROUP')->first();
        $response = $this->get(route('catalog.group', $group->id));

        $response->assertStatus(200);
        $response->assertSee('Growing of non-perennial crops');
        $response->assertSee('Growing of cereals');
    }

    public function test_catalog_class_returns_code_detail()
    {
        $class = Nace2027::where('level', 'CLASS')->first();
        $response = $this->get(route('catalog.class', $class->id));

        $response->assertStatus(200);
        $response->assertSee('Growing of cereals');
        $response->assertSee('Test class description');
    }
}
