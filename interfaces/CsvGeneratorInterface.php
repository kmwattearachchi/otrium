<?php

interface CsvGeneratorInterface
{
    /**
     * Starting method of the export process. Once execute this, it will generate 2 csv reports.
     *
     *  1. 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)
     *  2. 7 days turnover per day. (Day, total turnover(excluding VAT) per day)
     *
     * @throws Exception
     */
    public function executeReport();
}
