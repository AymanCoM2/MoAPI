<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;

Route::get("/sql-within-range", function () {
    $invoiceData  = AlJouaiRequests::SQL_get_data_range("v", "");
    return response()->json($invoiceData);
});


Route::get("/sql-specific-date", function () {
    // SQL_get_data_range
    $invoiceData  = AlJouaiRequests::SQL_get_data_specific_date("");
    return response()->json($invoiceData);
});
