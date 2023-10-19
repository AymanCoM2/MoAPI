<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Make the Connection With the "100" DB on the YZ network First and Get Data 
// Then Filter Data and Parse them Into Json 
// First Of all We Will Need An endpoint as a Sample Data For the json Data 
// & /api/sample
Route::get('/sample/{phoneNumber}', function (Request $request) {
    // 0553142429
    $inputPhoneNumber = $request->phoneNumber;
    $phoneQuery  = "SELECT * FROM MobileNumber WHERE [Mobile Number] = '" . $inputPhoneNumber . "'";
    // You Can Get the Phone Numbers By Selecting using DocEntry Not the Phone Number 
    $phoneData = DB::connection('sqlsrv')->select($phoneQuery);
    // $phoneData Variable  Gets you An array Or DocEntry for ALL invoices For this Customer 
    $userDocEntry  = $phoneData[0]->DocEntry; // ^ this only Get You One InvoiceDocEntry 
    $allUserInvoices  = [];
    foreach ($phoneData as $key => $value) {
        $allUserInvoices[] = $value->DocEntry;
    }
    // * //////////////////////////////////////////////////////////////////
    // * //////////////////////////////////////////////////////////////////
    $generalInfoQuery = "
    SELECT T0.DocEntry, CASE WHEN ISNULL(T0.LicTradNum, '') = '' THEN N'فاتورة ضريبية مبسطة'
    ELSE N'فاتورة ضريبية' END AS 'InvoiceTitle',
    T0.CardName, T0.CardCode , T0.LicTradNum , T0.DocDate , T0.DocDueDate ,
    CONCAT(ISNULL(N1.SeriesName,'') ,T0.DocNum )  'DocNum',
    (T0.DocTotal + T0.DiscSum - T0.RoundDif - T0.VatSum) 'NetTotalBefDisc',
    T0.DiscPrcnt , T0.DiscSum ,
    (T0.DocTotal - T0.RoundDif - T0.VatSum) 'NetTotalBefVAT',
    T0.VatSum , T0.DocTotal , T00.U_NAME , T0.Comments 
    FROM AljouaiT.DBO.OINV T0
    LEFT JOIN AljouaiT.DBO.NNM1 N1 ON N1.Series = T0.Series
    LEFT JOIN  AljouaiT.DBO.OUSR T00 ON T0.USERSIGN = T00.INTERNAL_K
    WHERE 
    T0.CANCELED ='N' and T0.DocEntry = " . $userDocEntry; // & The Number We Got From the User Phone 
    $generalData = DB::connection('sqlsrv')->select($generalInfoQuery);
    // ! Looping Through each DocEntry and Get the Data and Check the Data 
    // And Check If the Date Is Valid Or Not   ; 
    // * //////////////////////////////////////////////////////////////////
    // * //////////////////////////////////////////////////////////////////
    $itemsQuery = "
        SELECT
        T0.DocNum , T1.DocEntry, T1.ItemCode, T1.Dscription, T1.Quantity , T1.unitMsr , L0.Location ,
        T1.PriceBefDi , T1.DiscPrcnt , T1.Price , 
        ROUND(T1.Price * T1.Quantity,2) 'TotalBefVAT',
        ROUND(ROUND(T1.Price * T1.Quantity,2) * 1.05 ,2) 'TotalAftVAT'
        FROM 
        (TM.DBO.OINV T0 inner join TM.DBO.INV1 T1 on T1.DocEntry= T0.DocEntry)
        LEFT JOIN (TM.DBO.OWHS W0 LEFT JOIN TM.DBO.OLCT L0 ON W0.Location = L0.Code)
        ON W0.WhsCode = T1.WhsCode
        WHERE T1.DocEntry IN (SELECT T0.DocEntry FROM TM.DBO.OINV T0 WHERE T0.CANCELED ='N') and 
        T1.DocEntry = " . $userDocEntry;
    $itemsData = DB::connection('sqlsrv')->select($itemsQuery);
    // * //////////////////////////////////////////////////////////////////
    // * //////////////////////////////////////////////////////////////////
    return response()->json([
        'allUserInvoices' => $allUserInvoices,
    ]);
});


/**
 *  1- Endpoint for the Current Month Invoices 
 * 
 *  2- Invoice For a Certain Range 
 * 
 *  3- Endpoint to get the DocEntry and Return the Items Of this invoice 
 * 
 *  4- Endpoint to Get invoice For a "Certain Specific Date" 
 * 
 *  5- 
 * 
 */
