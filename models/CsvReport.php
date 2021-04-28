<?php

require 'interfaces/CsvReportInterface.php';

class CsvReport extends DBConnection implements CsvReportInterface
{
    /**
     * DB object
     *
     * @var PDO
     */
    private $db = null;

    /**
     * Initialize the local DB object from parent DB connect.
     */
    function __construct()
    {
        try {
            $this->db = parent::connect();
        } catch (Exception $ex) {
            throw new \PDOException($ex->getMessage(), (int)$ex->getCode());
        }
    }

    /**
     * Generate report of - 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *
     * @param $from
     * @param $to
     * @return array
     * @throws Exception
     */
    public function lastSevenDaysTurnOverPerBrandReport($from, $to)
    {
        try {
            //Per day column header texts
            $dateHeaders = [];

            //Generate per day turnover
            $stmt = $this->db->query("
                SELECT
                DISTINCT
                     CONCAT(
                             'sum(case when DATE_FORMAT(g.date, ''%Y-%m-%d'') = ''',
                             dt,
                             ''' then round((g.turnover / " . VAT_PERCENTAGE . "),2) else 0 end) AS `',
                             dt, '`'
                         )
                from
                    (
                        select DATE_FORMAT(g.date, '%Y-%m-%d') as dt
                        from gmv g
                        where g.date between date('" . $from . "') and date('" . $to . "')
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
                sum(g.turnover) as turnover_per_brand_for_period_including_vat,
                round(    sum(g.turnover) - ((sum(g.turnover) / " . VAT_PERCENTAGE . "))     ,2) as turnover_per_brand_for_period_excluding_vat,
                " . $dateHeadersSqlAppendPart . "
                from brands b
                inner join gmv g on b.id = g.brand_id
                where g.date between date('" . $from . "') and date('" . $to . "')
                group by b.name"
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            throw new \PDOException($ex->getMessage(), (int)$ex->getCode());
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode());
        }
    }

    /**
     * Generate report of - 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     * @param $from - Date which report starts from
     * @param $to - Date which report end with
     * @return array
     * @throws Exception
     */
    public function lastSevenDaysTurnOverPerEachDay($from, $to)
    {
        try {
            //Generate other data required for the report
            $stmt = $this->db->query("
                            SELECT date_format(g.date,'%Y-%m-%d') as 'day',
                       round(sum(g.turnover) / " . VAT_PERCENTAGE . ", 2) as total_turnover_vat_excluded
                from brands b
                         inner join gmv g
                                    on b.id = g.brand_id
                where g.date between date('" . $from . "') and date('" . $to . "')
                group by g.date"
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            throw new \PDOException($ex->getMessage(), (int)$ex->getCode());
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage(), (int)$ex->getCode());
        }
    }
}