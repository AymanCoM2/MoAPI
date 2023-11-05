<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;



Route::get('/f/invoice/{docEntry}', function (Request $request) {
    $dateAsKey  = null;   // ^ This is the Date We Get from the Invoice and use it as key
    $finalCombination  = [];
    $invoiceNumber = $request->docEntry;
    $newArrayForDate = [];
    $newArrayForDate[$invoiceNumber] = [
        'Total' => AlJouaiRequests::getInvoiceDocTotal($invoiceNumber),
        'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($invoiceNumber),
        'Dates' => AlJouaiRequests::getInvoiceDatesOnly($invoiceNumber),
    ];
    // $invoiceData  = AlJouaiRequests::getSingleInvoiceTotalData($invoiceNumber);
    return response()->json([$dateAsKey => $finalCombination]);
});



















































Route::get('/f/last-five/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userDocs  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    return response()->json($userDocs);
});


Route::post('/f/get-invoices-within-range', function (Request $request) {
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
});


Route::post('/f/specific-date', function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $result = AlJouaiRequests::getInvoiceInDate($entriesAndDates, $specificDate);
    return response()->json([$result]);
});
