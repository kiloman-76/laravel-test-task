<?php


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Row;

class RowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function imported_rows_are_grouped_by_date()
    {
        Row::create([
            'id' => 1,
            'name' => 'Test User 1',
            'date' => '2025-01-01',
        ]);

        Row::create([
            'id' => 2,
            'name' => 'Test User 2',
            'date' => '2025-01-01',
        ]);

        Row::create([
            'id' => 3,
            'name' => 'Test User 3',
            'date' => '2025-01-02',
        ]);

        $response = $this->getJson('/api/rows');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '2025-01-01' => [
                    ['id', 'name', 'date'],
                    ['id', 'name', 'date'],
                ],
                '2025-01-02' => [
                    ['id', 'name', 'date'],
                ],
            ]);
    }
}
