<?php
ini_set('mssql.charset', 'utf-8');

try {
    $conn = new PDO("dblib:host=hostadress;dbname=dbname;charset=utf8", "user", "pw");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo $exception->getMessage();
    exit;
}