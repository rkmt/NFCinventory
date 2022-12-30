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

function setupGoogleSheet($keyFile) {
    $client = new Google_Client();
    $client->setAuthConfig($keyFile);
    $client->setApplicationName("NFC Tag Registration");
    $scopes = [Google_Service_Sheets::SPREADSHEETS];
    $client->setScopes($scopes);
    $sheet = new Google_Service_Sheets($client);
    return ($sheet);
}

?>

