<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AlJouaiRequests;

Route::get('/current-month/{phoneNumber}', function (Request $request) {
    $inputPhoneNumber = $request->phoneNumber;
    $userEntries  = AlJouaiRequests::getAllCustomerDocEntries($inputPhoneNumber);
    $userInvoicesDates  = AlJouaiRequests::getAllCustomerInvoicesDates($userEntries);
    $currentMonthInvoices  = AlJouaiRequests::getInvoicesOfCurrentMonth($userInvoicesDates);
    $otherArray  = [];
    foreach ($currentMonthInvoices as $key => $value) {
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
}); // * EndPoint#3 


Route::post('/verify', function (Request $request) {
    $jsonData = $request->json()->all();
    $userPhone = $jsonData['phone'];
    $userInvoice  = $jsonData['invoice'];
    $userInvoice = (string) $userInvoice;
    $entriesArray  = AlJouaiRequests::getAllCustomerDocEntries($userPhone);
    if (in_array($userInvoice, $entriesArray)) {
        return response()->json([
            "res" => 1
        ]);
    } else {
        return response()->json([
            "res" => 0
        ]);
    }
}); // * EndPoint#4
