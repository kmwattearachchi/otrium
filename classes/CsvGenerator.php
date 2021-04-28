<?php

require_once 'DBConnection.php';
require_once 'models/CsvReport.php';
require_once 'utils/CsvUtil.php';
require_once 'interfaces/CsvGeneratorInterface.php';

/**
 * Class CsvGenerator
 *
 * THis class will read the brands and gmv tables and export data needed for analytical purpose.
 */
class CsvGenerator extends DBConnection implements CsvGeneratorInterface
{
    /**
     * @var CsvReport|null
     */
    protected $csvModal = null;

    /**
     * CsvGenerator constructor.
     * Initialize the DB connection for internal use.
     */
    function __construct()
    {
        try {
            $this->csvModal = new CsvReport();
        }catch (Exception $ex){
            echo MSG_CSV_MODAL_INITIALIZATION_FAILED;
        }
    }

    /**
     * Starting method of the export process. Once execute this, it will generate 2 csv reports.
     *
     *  1. 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *  2. 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     * @throws Exception
     */
    public function executeReport()
    {
        $this->_exportToCsv(REPORT_FROM_DATE, REPORT_TO_DATE);
        echo MSG_DATA_EXPORT_SUCCESS;
    }

    /**
     * @param $start
     * @param $end
     * @param string $format
     * @return array|false[]|string[]
     */
    private function _getDatesFromRange($start, $end, $format='Y-m-d') {
        return array_map(function($timestamp) use($format) {
            return date($format, $timestamp);
        },
            range(strtotime($start) + ($start < $end ? 4000 : 8000), strtotime($end) + ($start < $end ? 8000 : 4000), 86400));
    }

    /**
     * This is the main function used to generate the csv for the given data
     *
     * @param string $from - Date which report starts from
     * @param string $to - Date which report end with
     * @throws Exception
     */
    private function _exportToCsv($from = REPORT_FROM_DATE, $to = REPORT_TO_DATE)
    {
        //Report - 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
        $lastSevenDaysPerBrandTurnOver = $this->csvModal->lastSevenDaysTurnOverPerBrandReport($from,$to);
        $this->_processDataResultAndExport($lastSevenDaysPerBrandTurnOver,LAST_7_DAYS_TURNOVER_PER_BRAND_FILE_NAME);

        //Report - 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
        $lastSevenDaysPerDayTurnOver = $this->csvModal->lastSevenDaysTurnOverPerEachDay($from,$to);
        $this->_processDataResultAndExport($lastSevenDaysPerDayTurnOver,LAST_7_DAYS_DAILY_TURNOVER_EXPORT_FILE_NAME);
    }

    /**
     * Process the row data array to support csv data export
     *
     * @param $rows - Result set to be exported
     * @param $executionDate - Report execution date
     * @param $exportFileName - Export file name
     */
    private function _processDataResultAndExport($rows, $exportFileName)
    {
        //If no data to export
        if(empty($rows)){
            die(MSG_NO_DATA_TO_EXPORT);
        }

        //prepare the column names for CSV file.
        $columnNames = array();
        if (!empty($rows)) {
            //Loop through the first row of the result and extract keys
            $firstRow = $rows[0];
            foreach ($firstRow as $colName => $val) {
                $columnNames[] = $colName;
            }
        }

        //Export the result to CSV
        CsvUtil::writeToCvsFile($columnNames,$rows,$exportFileName);
    }
}