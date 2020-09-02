<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use \Ramsey\Uuid\Uuid as RamseyUuid;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKey = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKey);
    }

    public function testCreate() {
        $category = Category::create([
            'name' => 'Test'
        ]);
        $category->refresh();
        $this->assertEquals('Test', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        
        $category = Category::create([
            'name' => 'Test',
            'description' => 'Desc Test'
        ]);
        $this->assertEquals('Desc Test', $category->description);
        
        $category = Category::create([
            'name' => 'Test',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);
        
        $category = Category::create([
            'name' => 'Test',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
        $this->assertEquals($category->id, RamseyUuid::fromString($category->id)->toString());
    }

    public function testUpdate() {
        $category = factory(Category::class)->create([
            'description' => 'Desc Test',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'Updated Test Name',
            'description' => 'Updated Description',
            'is_active' => true
        ];

        $category->update($data);

        foreach($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDelete() {
        $category = factory(Category::class)->create()->first();
        $category->delete();
        $this->assertEquals($category->updated_at, $category->deleted_at);
    }
}
