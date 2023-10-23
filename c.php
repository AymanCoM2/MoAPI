<?php

// $osInfo = php_uname();
// $firstWord = strtok($osInfo, ' ');

// if (strcasecmp($firstWord, 'Windows') === 0) {
//     $data = DB::connection('sqlsrv')->select($sap_Query);
// } else {
//     $serverName = "10.10.10.100";
//     $databaseName = "TM";
//     $uid = "ayman";
//     $pwd = "admin@1234";
//     $options = [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE => true,
//         "TrustServerCertificate" => true,
//     ];
//     $conn = new PDO("sqlsrv:server = $serverName; Database = $databaseName;", $uid, $pwd, $options);
//     $stmt = $conn->query($sap_Query);
//     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//         $data[] = $row; // Append each row to the $data array
//     }
// }
