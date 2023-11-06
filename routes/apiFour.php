<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;


function desiredFormat($docEntry)
{
    // Get the DocEntry and Return the Desired Format Needed For the Invoice 
    $dateAsKey  = null;   // ^ This is the Date We Get from the Invoice and use it as key
    $finalCombination  = [];
    $invoiceNumber = $docEntry;
    $newArrayForDate = [];
    $datesWholeObject  = AlJouaiRequests::getInvoiceDatesOnly($invoiceNumber);
    $newArrayForDate[] = [
        'Total' => AlJouaiRequests::getInvoiceDocTotal($invoiceNumber),
        'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($invoiceNumber),
        'Dates' => $datesWholeObject,
    ];
    $dateAsKey = $datesWholeObject['DocDate'];
    $finalCombination[$dateAsKey][$invoiceNumber] = $newArrayForDate;
    return $finalCombination;
} // ! Done and Generic For any Invoice 

// http://10.10.20.11:8000/api/f/invoice/353829
Route::get('/f/invoice/{docEntry}', function (Request $request) {
    $result  = desiredFormat($request->docEntry);
    return response()->json($result);
}); // * DONE 


// http://10.10.20.11:8000/api/f/last-five/0535575165
Route::get('/f/last-five/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userDocs  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    natsort($userDocs);
    $reversed = array_reverse($userDocs);
    $last5 = array_slice($reversed, 0, 5);
    $result = [];

    foreach ($last5 as $key => $value) {
        $result[] = desiredFormat($value);
    }

    $objectOfObjects = [];
    foreach ($result as $item) {
        $key = key($item);
        $value = current($item);
        $objectOfObjects[$key] = $value;
    }
    return response()->json($objectOfObjects);
}); // * DONE 


// http://10.10.20.11:8000/api/f/get-invoices-within-range
// ! POST
// {
//     "enddate": "2023-05-05",
//     "startdate": "2023-01-01",
//     "phone": "0535575165"
// }
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
    $r  = AlJouaiRequests::getInvoiceEntriesONLYInRange($entriesAndDates, $start, $end);
    $result = [];

    foreach ($r as $key => $value) {
        $result[] = desiredFormat($value);
    }
    $objectOfObjects = [];
    foreach ($result as $item) {
        $key = key($item);
        $value = current($item);
        $objectOfObjects[$key] = $value;
    }
    return response()->json($objectOfObjects);
}); // * DONE 



// http: //10.10.20.11:8000/api/f/specific-date
// ! POST
// {
//     "date": "2023-03-30",
//     "phone": "0535575165"
// }
Route::post('/f/specific-date', function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $r = AlJouaiRequests::getInvoicesInDateEntryONLY($entriesAndDates, $specificDate);
    $result = [];
    foreach ($r as $key => $value) {
        $result[] = desiredFormat($value);
    }
    $objectOfObjects = [];
    foreach ($result as $item) {
        $key = key($item);
        $value = current($item);
        $objectOfObjects[$key] = $value;
    }
    return response()->json($objectOfObjects);
}); // * DONE 
