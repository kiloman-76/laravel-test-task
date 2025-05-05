<?php


namespace App\Imports;

use App\Models\Row;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;


class RowsImport implements
    WithChunkReading,
    WithHeadingRow,
    WithValidation,
    ShouldQueue,
    SkipsOnFailure,
    ToModel

{
    use RemembersRowNumber;

    protected string $importKey;
    protected array $errors = [];
    protected int $maxRows = 0;

    public function __construct(string $importKey)
    {
        $this->importKey = $importKey;
    }

    public function model($row)
    {
        $currentRowNumber = $this->getRowNumber();

        Log::info("Импорт строки :{$row['name']}");

        if (Row::where('external_id', $row['id'])->exists()) {
            $this->errors[$currentRowNumber][] = "Duplicate id";
        }
        if (!empty($this->errors)) {
            $errString = '';
            foreach ($this->errors as $line => $errors){
                $errString .= $line . ' - ' . implode("; ", $errors) . "\n";
            }
            Storage::disk('local')->append('result.txt', $errString);
            $this->errors = [];
            return null;
        }

        Redis::incr("import_progress:{$this->importKey}");
        return new Row([
            'external_id' => $row['id'],
            'name' => $row['name'],
            'date' => \Carbon\Carbon::createFromFormat('d.m.Y', $row['date'])->format('Y-m-d'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id'   => 'required|integer|min:1',
            'name' => 'required|regex:/^[A-Za-z ]+$/',
            'date' => 'required|date_format:d.m.Y|before:now',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[$failure->row()][] = implode('; ', $failure->errors());
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }


}

