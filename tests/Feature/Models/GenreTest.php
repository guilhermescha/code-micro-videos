<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use \Ramsey\Uuid\Uuid as RamseyUuid;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;
    
    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genre = Genre::all();
        $this->assertCount(1, $genre);
        $genreKey = array_keys($genre->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $genreKey);
    }

    public function testCreate() {
        $genre = Genre::create([
            'name' => 'Test'
        ]);
        $genre->refresh();
        $this->assertEquals('Test', $genre->name);
        $this->assertTrue($genre->is_active);
        
        $genre = Genre::create([
            'name' => 'Test',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
        
        $genre = Genre::create([
            'name' => 'Test',
            'is_active' => true
        ]);
        $this->assertTrue($genre->is_active);
        $this->assertEquals($genre->id, RamseyUuid::fromString($genre->id)->toString());
    }

    public function testUpdate() {
        $genre = factory(Genre::class)->create([
            'name' => 'Test Genre',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'Updated Test Genre',
            'is_active' => true
        ];

        $genre->update($data);

        foreach($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete() {
        $genre = factory(Genre::class)->create()->first();
        $genre->delete();
        $this->assertEquals($genre->updated_at, $genre->deleted_at);
    }
}
