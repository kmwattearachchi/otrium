<?php

use PHPUnit\Framework\TestCase;
require_once 'classes/CsvGenerator.php';

/**
 * Class CsvTest
 */
class CsvTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        //Remove test files after completing unit tests
        register_shutdown_function(function() {
            if(file_exists(EXPORT_FILE_BASE_PATH.'/'.LAST_7_DAYS_TURNOVER_PER_BRAND_FILE_NAME)) {
                unlink(EXPORT_FILE_BASE_PATH.'/'.LAST_7_DAYS_TURNOVER_PER_BRAND_FILE_NAME);
            }

            if(file_exists(EXPORT_FILE_BASE_PATH.'/'.LAST_7_DAYS_DAILY_TURNOVER_EXPORT_FILE_NAME)) {
                unlink(EXPORT_FILE_BASE_PATH.'/'.LAST_7_DAYS_DAILY_TURNOVER_EXPORT_FILE_NAME);
            }
        });
    }

    /**
     * Should call each file test
     *
     * @throws Exception
     */
    private function _generateCsv()
    {
        $csvGenerator = new CsvGenerator();
        try {
            $csvGenerator->executeReport();
        } catch (Exception $e) {
            throw new \Exception($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Check files are generated under the given folder
     *
     * @throws Exception
     */
    public function testCsvFilesGenerated()
    {
        $this->_generateCsv();
        $this->assertFileExists(EXPORT_FILE_BASE_PATH.'/last_7_days_daily_turnover.csv',"given filename doesn't exists");
        $this->assertFileExists(EXPORT_FILE_BASE_PATH.'/last_7_days_turnover_per_brand.csv',"given filename doesn't exists");
    }

    /**
     * Check last_7_days_daily_turnover.csv contents
     */
    public function testLast7DaysDailyTurnoverCsvFileContent()
    {
        try {
            $this->_generateCsv();
        } catch (Exception $e) {
        }
        $fileContent =  file_get_contents('Reports/last_7_days_daily_turnover.csv');
        $this->assertStringContainsString('day',$fileContent);
        $this->assertStringContainsString('total_turnover_vat_excluded',$fileContent);
    }

    /**
     *  Check last_7_days_turnover_per_brand.csv contents
     */
    public function testLast7DaysTurnoverPerBrandCsvFileContent()
    {
        try {
            $this->_generateCsv();
        } catch (Exception $e) {
        }
        $fileContent =  file_get_contents('Reports/last_7_days_turnover_per_brand.csv');
        $this->assertStringContainsString('brand_name',$fileContent);
        $this->assertStringContainsString('turnover_per_brand_for_period_including_vat',$fileContent);
        $this->assertStringContainsString('turnover_per_brand_for_period_excluding_vat',$fileContent);
    }
}