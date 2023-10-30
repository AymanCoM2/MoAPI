<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;

Route::get('/user-docs/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userDocs  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    return response()->json($userDocs);
}); // * EndPoint#1 

Route::get('/invoice/{docEntry}', function (Request $request) {
    $invoiceNumber = $request->docEntry;
    $invoiceData  = AlJouaiRequests::getSingleInvoiceTotalData($invoiceNumber);
    return response()->json($invoiceData);
}); // * EndPoint#2 