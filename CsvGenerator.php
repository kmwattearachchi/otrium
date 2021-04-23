<?php

require 'DBConnection.php';

/**
 * Class CsvGenerator
 *
 * THis class will read the brands and gmv tables and export data needed for analytical purpose.
 */
class CsvGenerator extends DBConnection
{
    /**
     * Db Connection
     * @var PDO|null
     */
    protected $db = null;

    /**
     * CsvGenerator constructor.
     * Initialize the DB connection for internal use.
     */
    function __construct()
    {
        $con = new DBConnection();
        $this->db = $con->db;
    }

    /**
     * Starting method of the export process. Once execute this, it will generate 2 csv reports.
     *
     *  1. 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *  2. 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     */
    public function executeDailyRoutine()
    {
        $reportExecutionDays = $this->_getDatesFromRange(REPORT_FROM_DATE,REPORT_TO_DATE);

        foreach ($reportExecutionDays as $day){
            //Get the last 7 days from the execution date - $reportFromDate
            $reportFromDate = date('Y-m-d', strtotime('-7 days', strtotime($day)));
            $reportToDate = date('Y-m-d', strtotime($day.' - 1 days'));

            //Execute the reporting process
            $this->_exportToCsv($reportFromDate, $reportToDate , $day);
        }

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
     */
    private function _exportToCsv($from = REPORT_FROM_DATE, $to = REPORT_TO_DATE,$executionDate)
    {
        //Report - 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
        $lastSevenDaysPerBrandTurnOver = $this->_lastSevenDaysTurnOverPerBrandReport($from,$to);
        $this->_processDataResultAndExport($lastSevenDaysPerBrandTurnOver,$executionDate,LAST_7_DAYS_TURNOVER_PER_BRAND_FILE_NAME);

        //Report - 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
        $lastSevenDaysPerDayTurnOver = $this->_lastSevenDaysTurnOverPerEachDay($from,$to);
        $this->_processDataResultAndExport($lastSevenDaysPerDayTurnOver,$executionDate,LAST_7_DAYS_DAILY_TURNOVER_EXPORT_FILE_NAME);
    }

    /**
     * Generate report of - 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *
     * @param $from
     * @param $to
     * @return array
     */
    private function _lastSevenDaysTurnOverPerBrandReport($from, $to)
    {
        //Per day column header texts
        $dateHeaders = [];

        //Generate per day turnover
        $stmt = $this->db->query("
            SELECT
            DISTINCT
                 CONCAT(
                         'sum(case when DATE_FORMAT(g.date, ''%Y-%m-%d'') = ''',
                         dt,
                         ''' then round((g.turnover / 1.21),2) else 0 end) AS `',
                         dt, '`'
                     )
            from
                (
                    select DATE_FORMAT(g.date, '%Y-%m-%d') as dt
                    from gmv g
                    where g.date between date('".$from."') and date('".$to."')
                    order by g.date
            
                ) temp_tbl;"
        );

        $row = $stmt->fetchAll();
        foreach ($row as $key => $item) {
            foreach ($item as $itemKey => $itemValue) {
                array_push($dateHeaders, $itemValue);
            }
        }

        //Create sting need to append to the SQL
        $dateHeadersSqlAppendPart = implode(",", $dateHeaders);

        //Generate other data required for the report
        $stmt = $this->db->query("
            SELECT b.name brand_name,
            " . $dateHeadersSqlAppendPart . "
            from brands b
            inner join gmv g on b.id = g.brand_id
            where g.date between date('".$from."') and date('".$to."')
            group by b.name"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate report of - 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     * @param $from - Date which report starts from
     * @param $to - Date which report end with
     * @return array
     */
    private function _lastSevenDaysTurnOverPerEachDay($from, $to)
    {
        //Generate other data required for the report
        $stmt = $this->db->query("
                            SELECT date_format(g.date,'%Y-%m-%d') as 'day',
                       round(sum(g.turnover) / 1.21, 2) as total_turnover_vat_excluded
                from brands b
                         inner join gmv g
                                    on b.id = g.brand_id
                where g.date between date('".$from."') and date('".$to."')
                group by g.date"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Process the row data array to support csv data export
     *
     * @param $rows - Result set to be exported
     * @param $executionDate - Report execution date
     * @param $exportFileName - Export file name
     */
    private function _processDataResultAndExport($rows, $executionDate, $exportFileName)
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
        $this->_writeToCvsFile($columnNames,$rows,$executionDate,$exportFileName);
    }

    /**
     * This method use to write data to csv.
     *
     * @param $columnNames - CSV column header names
     * @param $rows - CSV row data
     */
    private function _writeToCvsFile($columnNames, $rows,$executionDate,$exportFileName)
    {
        $exportFilePath = EXPORT_FILE_BASE_PATH.'/'.$executionDate;
        try {
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

//Execute the report
$csvGenerator = new CsvGenerator();
$csvGenerator->executeDailyRoutine();