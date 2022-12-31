<?php

function getHostHash() {
    $host = gethostbyaddr(getenv('REMOTE_ADDR'));
    if(preg_match('/docomo/',$host)) {
        $host = $_SERVER['HTTP_X_DCMGUID'];
    } elseif(preg_match('/ezweb/',$host)) {
        $host = $_SERVER['HTTP_X_UP_SUBNO'];
    } elseif(preg_match('/jp-d.ne.jp|jp-h.ne.jp|jp-t.ne.jp|jp-c.ne.jp|jp-k.ne.jp|jp-r.ne.jp|jp-n.ne.jp|jp-s.ne.jp|jp-q.ne.jp/',$host)) {
        $host = $_SERVER['HTTP_X_JPHONE_UID'];
    }
    return (substr(md5($host), 0, 8));
    //return ($host);
}

function sheetname2id($sheet, $sheet_id, $name) {
    $response = $sheet->spreadsheets->get($sheet_id);
    foreach($response->getSheets() as $sh) {
        if (strcmp($sh['properties']['title'], $name) == 0) {
            $sid = $sh['properties']['sheetId'];
            return $sid;
        }
    }
    return -1;
}

function setupGoogleSheet($keyFile) {
    $client = new Google_Client();
    $client->setAuthConfig($keyFile);
    $client->setApplicationName("NFC Tag Registration");
    $scopes = [Google_Service_Sheets::SPREADSHEETS];
    $client->setScopes($scopes);
    $sheet = new Google_Service_Sheets($client);
    return ($sheet);
}

// $range: "A2:B2"  $values  [["a", "b"]]
function set_cell($sheet, $sheet_id, $range, $values) {
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);
    $response = $sheet->spreadsheets_values->update(
        $sheet_id, // 作成したスプレッドシートのIDを入力
        $range, //range
        $body, //データ
        ["valueInputOption" => 'USER_ENTERED']
    );    
    return $response;
}

function get_cell($sheet, $obj_sheet_id, $range) {
    $response = $sheet->spreadsheets_values->get(
        $obj_sheet_id,
        $range
    );
    $values = $response->getValues();
    return $values;
}

?>

