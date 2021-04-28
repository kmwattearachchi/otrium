<?php
require_once 'classes/CsvGenerator.php';

//Execute the report
$csvGenerator = new CsvGenerator();
try {
    $csvGenerator->executeReport();
} catch (Exception $e) {
    echo MSG_UNABLE_TO_EXECUTE_REPORT." : ". $e->getMessage();
}
