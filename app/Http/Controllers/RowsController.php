<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Row;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RowsImport;

class RowsController extends Controller
{
    public function index(Request $request)
    {
        $rows = Row::all()->groupBy('date');
        var_dump($rows->toArray());
        exit();
    }
}
