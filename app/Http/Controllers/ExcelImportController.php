<?php

namespace App\Http\Controllers;

use App\Imports\RowsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ProcessExcelChunk;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redis;

class ExcelImportController extends Controller
{
    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);

        $uuid = Str::uuid();
        $path = $request->file('file')->storeAs('import', "$uuid.xlsx");
        $importId = uniqid('import_');

        Redis::set("import_progress:$importId", 0);
        Excel::import(new RowsImport($importId), storage_path("app/private/" . $path));
        Storage::disk('local')->put('import/result.txt', '');

        return response()->json(['status' => 'working', 'uuid' => $uuid]);
    }

}
