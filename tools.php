<?php
// phpinfo();

ini_set('display_startup_errors', true);
error_reporting(E_ALL);
ini_set('display_errors', true);

function printr($elmt) {
    echo "<pre>" . print_r($elmt) ."</pre>";
}

function vdump($elmt) {
    echo "<pre>" . var_dump($elmt) . "</pre>";
}

function uvd($elmt) {
    echo "var_dump() : <br>";
    var_dump($elmt);
    echo "<br>";
    echo "print() : <br>";
    print($elmt);
    echo "<br>";
    echo "printf() : <br>";
    printf($elmt);
    echo "<br>";
    echo "print_r() : <br>";
    print_r($elmt);
}

function displaySqlErrors() {
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
}

function api_error() {
    header("Content-Type: text/html; charset=utf-8");
    echo "L'application a rencontré une erreur. Nous nous excusons pour ce désagrément. Veuillez contacter le secrétariat";
}
