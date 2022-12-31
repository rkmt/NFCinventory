<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<link rel="stylesheet" href="/style.css">
<title>NFC Tag Inventory</title>
</head>
<body>

<h2>Initialize Google Spreadsheet</h2>

<?php

include("./settings.php");
include("./common.php");

// setup Google Spreadsheet handler
require __DIR__. '/vendor/autoload.php';
$keyFile = __DIR__. "/credentials.json";
$sheet = setupGoogleSheet($keyFile);



function init_sheet($sheet, $sheet_id, $column) {
    $len = count($column);

    //echo("<p>VAL:".$len);

    $values = get_cell($sheet, $sheet_id, $sheet1_name."!A1:A1");

    //echo("<p>A1A1 = ".$values[0][0]);

    if (strcmp($values[0][0], "ID") == 0) { // already initialized
        echo("<p>sheet_id: ".$sheet_id." is already initialized.");
        return;
    }

    $range = $sheet1.'!A1:'.chr(ord('A')+$len-1).'1';
    echo("<p>UPDATE: ".$range);
    
    set_cell($sheet, $sheet_id, $range, [$column]);

    echo("<P>CELL added");
    
    try {
        // リクエストデータ
        $request_data = [
            'repeatCell' => [
                'fields' => 'userEnteredFormat(backgroundColor)',
                'range' => [
                    'sheetId' => $shee1_name,
                    'startRowIndex' => 0,       // 行の開始位置
                    'endRowIndex' => 1,        // 行の終了位置
                    'startColumnIndex' => 0,    // 列の開始位置
                    'endColumnIndex' => $len,      // 列の終了位置
                ],
                'cell' => [
                    'userEnteredFormat' => [
                        'backgroundColor' => [  // 色はRGB形式
                            'red' => 180/255,
                            'green' => 180/255,
                            'blue' => 200/255
                        ]
                    ],
                ],
            ],
        ];
    
        $requests = [new \Google_Service_Sheets_Request($request_data)];
        $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
        $response = $sheet->spreadsheets->batchUpdate($sheet_id, $batchUpdateRequest);
    } catch (\Exception $e) {
        echo("<p>Can't initialize:<br>".$e);
    }
}


init_sheet($sheet, $obj_sheet_id, ['ID', 'Date', 'Time', 'Name', 'Type', 'Hash', 'Location', 'Update Date', 'Update Time']);
init_sheet($sheet, $log_sheet_id, ['ID', 'Date', 'Time', 'Name', 'Timestamp', 'Place', 'Hash']);

echo("<p>Init Sheets done.")

?>

</body>