<?php

namespace Tests\Feature;

use App\Models\Nace2027;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoUrlNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Seed data
        $section = Nace2027::create(['code' => 'J', 'title' => 'Information', 'level' => 'SECTION']);
        $division = Nace2027::create(['code' => '62', 'title' => 'Computer programming', 'level' => 'DIVISION', 'parent_id' => $section->id]);
        $group = Nace2027::create(['code' => '62.0', 'title' => 'IT services', 'level' => 'GROUP', 'parent_id' => $division->id]);
        $class = Nace2027::create(['code' => '62.01', 'title' => 'Software development', 'level' => 'CLASS', 'parent_id' => $group->id]);
    }

    public function test_section_seo_url()
    {
        $response = $this->get('/catalog/section-j');
        $response->assertStatus(200);
        $response->assertSee('Information');
    }

    public function test_division_seo_url()
    {
        $response = $this->get('/catalog/62');
        $response->assertStatus(200);
        $response->assertSee('Computer programming');
    }

    public function test_group_seo_url()
    {
        $response = $this->get('/catalog/62/62-0');
        $response->assertStatus(200);
        $response->assertSee('IT services');
    }

    public function test_class_seo_url()
    {
        $response = $this->get('/catalog/62/62-0/62-01');
        $response->assertStatus(200);
        $response->assertSee('Software development');
        $response->assertSee('J — Information');
        $response->assertSee('62');
        $response->assertSee('62.0');
    }

    public function test_invalid_hierarchy_returns_404()
    {
        // Try accessing group 62.0 under division 61
        $response = $this->get('/catalog/61/62-0');
        $response->assertStatus(404);
    }

    public function test_invalid_code_returns_404()
    {
        $response = $this->get('/catalog/62/62-9/62-99');
        $response->assertStatus(404);
    }

    public function test_section_link_generation()
    {
        $response = $this->get('/catalog');
        $response->assertSee('/catalog/section-j');
    }

    public function test_division_link_generation()
    {
        $response = $this->get('/catalog/section-j');
        $response->assertSee('/catalog/62');
    }

    public function test_group_link_generation()
    {
        $response = $this->get('/catalog/62');
        $response->assertSee('/catalog/62/62-0');
    }

    public function test_class_link_generation()
    {
        $response = $this->get('/catalog/62/62-0');
        $response->assertSee('/catalog/62/62-0/62-01');
    }
}
