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
     * This is the main function used to generate the csv for the given data
     *
     * @param string $from - Date which report starts from
     * @param string $to - Date which report end with
     */
    public function exportToCsv($from = REPORT_FROM_DATE, $to = REPORT_TO_DATE)
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
                         ''' then g.turnover else 0 end) AS `',
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
            SELECT b.name product_name,
            sum(g.turnover) as turnover_for_the_period,
            round(sum(g.turnover) / ".(1+(VAT_PERCENTAGE/100)).", 2) as vat_excluded," . $dateHeadersSqlAppendPart . "
            from brands b
            inner join gmv g on b.id = g.brand_id
            where g.date between date('".$from."') and date('".$to."')
            group by b.name"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        $this->_writeToCvsFile($columnNames,$rows);

        die(MSG_DATA_EXPORT_SUCCESS);
    }

    /**
     * This method use to write data to csv.
     *
     * @param $columnNames - CSV column header names
     * @param $rows - CSV row data
     */
    private function _writeToCvsFile($columnNames, $rows)
    {
        try {
            $file = fopen(EXPORT_FILE_NAME, 'w');
            array_unshift($rows, $columnNames);
            foreach ($rows as $result) {
                fputcsv($file, $result);
            }
            fclose($file);
        }catch (Exception $ex){
            die(MSG_FILE_EXPORT_FAILED);
        }
    }
}

//Execute the report
$csvGenerator = new CsvGenerator();
$csvGenerator->exportToCsv();