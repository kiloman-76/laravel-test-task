<?php

namespace Tests\Feature\Imports;

use App\Imports\RowsImport;
use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Validators\Failure;
use Tests\TestCase;

class RowsImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushall();
        Storage::fake('local');
    }

    public function test_import_valid_row_creates_model_and_increments_redis(): void
    {
        $import = new RowsImport('test');

        $row = [
            'id' => 1,
            'name' => 'Test User',
            'date' => '01.01.2024',
        ];

        $model = $import->model($row);

        $this->assertInstanceOf(Row::class, $model);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals(1, Redis::get('import_progress:test'));
    }

    public function test_duplicate_id_does_not_create_new_model(): void
    {
        Row::create([
            'external_id' => 1,
            'name' => 'Test User',
            'date' => '2025-01-01'
        ]);

        $import = new RowsImport('test');

        $row = [
            'id' => 1,
            'name' => 'Test User',
            'date' => '01.01.2025',
        ];

        $result = $import->model($row);

        $this->assertNull($result);
        $this->assertDatabaseCount('rows', 1);
    }

    public function test_validation_error_is_recorded_in_result_file(): void
    {
        $import = new RowsImport('test');

        $failures = [
            new Failure(
                5,
                'name',
                ['The name format is invalid.'],
                ['name' => '1234']
            ),
        ];

        $import->onFailure(...$failures);

        $row = [
            'id' => 1234,
            'name' => '1234',
            'date' => '01.01.2025',
        ];

        $import->model($row);

        Storage::disk('local')->assertExists('result.txt');
        $contents = Storage::disk('local')->get('result.txt');
        $this->assertStringContainsString('5 - The name format is invalid.', $contents);
    }

}
