<?php

require_once 'constants/Constants.php';
require_once 'interfaces/DBConnectionInterface.php';

/**
 * Class DBConnection
 *
 * Initialize the DB connection for the application
 */
abstract class DBConnection implements DBConnectionInterface
{
    /**
     * Return PDO object
     *
     * @return PDO
     */
    public function connect()
    {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHAR_SET;
        $dbOptions = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            return new PDO($dsn, DB_UNAME, DB_PASSWORD, $dbOptions);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}