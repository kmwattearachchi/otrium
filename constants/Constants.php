<?php

//Database configurations
define('DB_HOST','localhost');
define('DB_CHAR_SET','utf8mb4');
define('DB_NAME','otrium2');
define('DB_UNAME','root');
define('DB_PASSWORD','123');

//Export File configurations
define('EXPORT_FILE_BASE_PATH','Reports');
define('LAST_7_DAYS_TURNOVER_PER_BRAND_FILE_NAME','last_7_days_turnover_per_brand.csv');
define('LAST_7_DAYS_DAILY_TURNOVER_EXPORT_FILE_NAME','last_7_days_daily_turnover.csv');

//VAT % Configuration
define('VAT_PERCENTAGE',21);

//Report Generate period
define('REPORT_FROM_DATE','2018-05-01');
define('REPORT_TO_DATE','2018-05-07');

//Error messages
define('MSG_UNABLE_TO_EXECUTE_REPORT','Report execution failed.');
define('MSG_NO_DATA_TO_EXPORT','No data to export.');
define('MSG_DATA_EXPORT_SUCCESS','Data Exported Successfully.');
define('MSG_FILE_EXPORT_FAILED','Unable to write the csv file.');
define('MSG_CSV_MODAL_INITIALIZATION_FAILED','Unable to initialize the csv modal.');
