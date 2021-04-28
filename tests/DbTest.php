<?php

use PHPUnit\Framework\TestCase;
require_once 'classes/DBConnection.php';

/**
 * Class DbTest
 */
class DbTest extends TestCase
{
    /**
     * DB connection test, should return Object
     */
    public function testDbConnection()
    {
        $this->assertIsObject(
            DBConnection::connect()
        );
    }
}