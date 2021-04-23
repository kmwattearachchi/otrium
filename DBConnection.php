<?php

require 'Constants.php';

/**
 * Class DBConnection
 *
 * Initialize the DB connection for the application
 */
class DBConnection
{
    /**
     * @var PDO|null
     */
    protected $db = null;

    /**
     * DBConnection constructor.
     * Initialize the DB connection of the application
     */
    function __construct()
    {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHAR_SET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->db = new PDO($dsn, DB_UNAME, DB_PASSWORD, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}