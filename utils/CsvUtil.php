<?php
require_once 'constants/Constants.php';

class CsvUtil
{
    /**
     * This method use to write data to csv.
     *
     * @param $columnNames - CSV column header names
     * @param $rows - CSV row data
     * @param $executionDate
     * @param $exportFileName
     */
    public static function writeToCvsFile($columnNames, $rows,$exportFileName)
    {
        try {
            $exportFilePath = EXPORT_FILE_BASE_PATH;

            if (!is_dir($exportFilePath)) {
                mkdir($exportFilePath, 0777, true);
            }

            $file = fopen($exportFilePath.'/'.$exportFileName, 'w');
            array_unshift($rows, $columnNames);
            foreach ($rows as $result) {
                fputcsv($file, $result);
            }
            fclose($file);
        }catch (Exception $ex){
            echo MSG_FILE_EXPORT_FAILED;
        }
    }
}
