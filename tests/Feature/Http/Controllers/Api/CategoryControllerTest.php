<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow() {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidData() {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertInvalidationRequired($response);
        $data = [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ];
            
        $response = $this->json('POST', route('categories.store'), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $category = factory(Category::class)->create();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    private function assertInvalidationRequired(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertInvalidationMax(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    private function assertInvalidationBoolean(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStore() {
        $data = [
            'name' => 'Test Category',
            'is_active' => true
        ];
        $response = $this->json('POST', route('categories.store'), $data);
        $category = Category::find($response->json('id'));
        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => false
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'Test Description'
        ]);
    }

    public function testUpdate() {

        $category = factory(Category::class)->create([
            'description' => 'Test Description',
            'is_active' => false
        ]);

        $data = [
            'name' => 'Test Category',
            'description' => 'Updated Description',
            'is_active' => true
        ];
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), $data);
        $category = Category::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'description' => 'Updated Description'
            ]);
            
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => 'Test Category',
            'description' => ''
        ]);
        $response
            ->assertJsonFragment([
                'description' => null
            ]);
    }

    public function testDelete() {
        $category = factory(Category::class)->create();
        $id = $category->id;
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $id]));
        $response
            ->assertStatus(204);
            
        $category = Category::find($id);
        $this->assertNull($category);
    }
}
