<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



// First Of all We Will Need An endpoint as a Sample Data For the json Data 
// & /api/sample
Route::get('/sample', function (Request $request) {
    return response()->json([
        'name' => 'Abo Salah'
    ]);
});
