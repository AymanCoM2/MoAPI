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
    // Standatd MOAPI 
    $inputPhoneNumber = $request->phoneNumber;
    $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    natsort($userEntries);
    $reversed  = array_reverse($userEntries);
    $lastFiveArray  = array_slice($reversed, 0, 5);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($lastFiveArray);
    $otherArray  = [];
    foreach ($userInvoicesDates as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }
    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
                'Dates' => AlJouaiRequests::getInvoiceDatesOnly($docEntry),
            ];
            if (isset($newContainer[$date])) {
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            } else {
                $newContainer[$date] = [];
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            }
        }
    }
    return response()->json($newContainer); // Data is Now Sorted 
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
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($r);
    $otherArray  = [];
    foreach ($userInvoicesDates as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }
    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
                'Dates' => AlJouaiRequests::getInvoiceDatesOnly($docEntry),
            ];
            if (isset($newContainer[$date])) {
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            } else {
                $newContainer[$date] = [];
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            }
        }
    }
    return response()->json($newContainer); // Data is Now Sorted 
}); // * DONE 


// http: //10.10.20.11:8000/api/f/specific-date
// ! POST
// {
//     "date": "2023-10-28",
//     "phone": "0535575165"
// }
Route::post('/f/specific-date', function (Request $request) {
    $jsonData = $request->json()->all();
    $specificDate  = $jsonData['date'];
    $userPhone  = $jsonData['phone'];
    $userDocEntries  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    $entriesAndDates = AlJouaiRequests::getAllCustomerInvoicesDates($userDocEntries);
    $r = AlJouaiRequests::getInvoicesInDateEntryONLY($entriesAndDates, $specificDate);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($r);
    $otherArray  = [];
    foreach ($userInvoicesDates as $key => $value) {
        $newKey  = $value['DocDate'];
        if (isset($otherArray[$newKey])) {
            $otherArray[$newKey][] = $key;
        } else {
            $kwys = [];
            $kwys[] = $key;
            $otherArray[$newKey]  = $kwys;
        }
    }

    ksort($otherArray);
    $newArrayForDate = [];
    $newContainer = [];
    foreach ($otherArray as $date => $arrOfEntries) {
        foreach ($arrOfEntries as $index => $docEntry) {
            $newArrayForDate[$docEntry] = [
                'Total' => AlJouaiRequests::getInvoiceDocTotal($docEntry),
                'NumberOfItems' => AlJouaiRequests::getCountOfNumbers($docEntry),
                'Dates' => AlJouaiRequests::getInvoiceDatesOnly($docEntry),
            ];
            if (isset($newContainer[$date])) {
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            } else {
                $newContainer[$date] = [];
                $newContainer[$date][$docEntry][] = $newArrayForDate[$docEntry];
            }
        }
    }
    return response()->json($newContainer); // Data is Now Sorted 
}); // * DONE 



Route::get('/qr/{docEntry}', function (Request $request) {
    $entry  = $request->docEntry;
    $qr  =  AlJouaiRequests::getQrCode($entry);
    return response()->json($qr);
}); // * DONE 



Route::get('/toto/{docEntry}', function (Request $request) {
    $entry  = $request->docEntry;
    $total  =  AlJouaiRequests::getNumberOfItemsInvoice($entry);
    $total2  =  AlJouaiRequests::getCountOfNumbers($entry);

    return response()->json([
        'total_1' => $total,
        'total_2' => $total2
    ]);
}); // * DONE 
