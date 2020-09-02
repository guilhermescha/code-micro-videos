<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow() {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidData() {
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequired($response);
        $data = [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ];
            
        $response = $this->json('POST', route('genres.store'), $data);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), $data);
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
            'name' => 'Test Genre',
            'is_active' => true
        ];
        $response = $this->json('POST', route('genres.store'), $data);
        $genre = Genre::find($response->json('id'));
        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'Test Genre',
            'is_active' => false
        ]);
        $response->assertJsonFragment([
            'is_active' => false
        ]);
    }

    public function testUpdate() {

        $genre = factory(Genre::class)->create([
            'name' => 'Test Genre',
            'is_active' => false
        ]);

        $data = [
            'name' => 'Updated Test Genre',
            'is_active' => true
        ];
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), $data);
        $genre = Genre::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'Updated Test Genre',
                'is_active' => true
            ]);
    }

    public function testDelete() {
        $genre = factory(Genre::class)->create();
        $id = $genre->id;
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $id]));
        $response
            ->assertStatus(204);
            
        $genre = Genre::find($id);
        $this->assertNull($genre);
    }
}
