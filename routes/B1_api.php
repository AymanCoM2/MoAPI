<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;

Route::post('/get-invoices-within-range', function (Request $request) {
    $jsonData = $request->json()->all();
    $start = $jsonData['startdate'];
    $phone = $jsonData['phone'];
    $end = $jsonData['enddate'];
    if (isset($end)) {
        $end  = $jsonData['enddate'];
    } else {
        $end = date("Y-m-d");
    }
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($phone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $result  = AlJouaiRequests::getInvoicesInRange($entriesAndDates, $start, $end);
    return response()->json([$result]);
}); // * EndPoint#3


Route::post('/specific-date', function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $result = AlJouaiRequests::getInvoiceInDate($entriesAndDates, $specificDate);
    return response()->json([$result]);
}); // * EndPoint#4 
