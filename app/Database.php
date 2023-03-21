<?php

namespace App\Connection;

class Database {
    public object $db;

    public function __construct()
    {
        // Obliga a utilizar el modo de reporte recomendado
        include 'database/creds.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            require('creds.php');
            $this -> db = new mysqli($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
            $this -> db -> set_charset($charset);
        } catch (mysqli_sql_exception $e) {
            throw new mysqli_sql_exception($e -> getMessage(), $e -> getCode());
        } finally {
            unset($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
        }
    }
}