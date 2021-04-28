<?php

/**
 * Interface CsvReportInterface
 */
interface CsvReportInterface
{
    /**
     * Generate report of - 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *
     * @param $from
     * @param $to
     * @return array
     */
    public function lastSevenDaysTurnOverPerBrandReport($from, $to);

    /**
     * Generate report of - 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     * @param $from - Date which report starts from
     * @param $to - Date which report end with
     * @return array
     */
    public function lastSevenDaysTurnOverPerEachDay($from, $to);
}