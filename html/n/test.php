<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no">
<link rel="stylesheet" href="/style.css">
<title>NFC Tag Registration Result</title>
</head>
<body>

<?php

include("./settings.php");
include("./common.php");
echo("LOG=".$log_sheet_id);

date_default_timezone_set('Asia/Tokyo');

require __DIR__. '/vendor/autoload.php';
$keyFile = __DIR__. "/credentials.json";

$sheet = setupGoogleSheet($keyFile);

// もっとも最近の場所の名前を得る
function find_place($sheet, $sheet_id, $ts, $time_diff) {
    $response = $sheet->spreadsheets_values->get(
        $sheet_id,
        'Sheet1!A:F'
    );
    $values = $response->getValues();
    
    $id = "";
    foreach (array_reverse($values) as $val) {
        //echo(var_dump($val));
        if ($ts - $val[4] < 3600*5 and (strcmp($val[5], "place") == 0 or strcmp($val[5], "box")==0)) {
            $id = $val[0];
            $placename = $val[3];
            return ($placename);
        }
    }
    return NULL;
}

// $pos: eg., 'G5' 
function cet_cell($sheet, $sheet_id, $pos, $val) {
    $values = [[$val]];
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);
    $response = $sheet->spreadsheets_values->update(
        $sheet_id, // 作成したスプレッドシートのIDを入力
        'Sheet1!'.$pos.":".$pos, //range
        $body, //データ
        ["valueInputOption" => 'USER_ENTERED']
    );    
    return $response;
}
    

$placename = find_place($sheet, $log_sheet_id, $ts, 3600*5);
echo("<p>Place/Box:".$placename);
if (isset($placename)) {
    $objid = "f3ff";
    $response = $sheet->spreadsheets_values->get(
        $obj_sheet_id,
        'Sheet1!A:A'
    );
    $values = $response->getValues();
    echo("<p>GetSheet2");
    $cell_idx = 0;
    foreach ($values as $val) {
        echo(var_dump($val));
        $cell_idx = $cell_idx + 1;
        if (strcmp($val[0], $objid) == 0) {
            break;
        }
    }

    echo ("<p>CELL=>:".$cell_idx);

    //cet_cell($sheet, $obj_sheet_id, 'G'.$cell_idx, $placename);
}

?>

