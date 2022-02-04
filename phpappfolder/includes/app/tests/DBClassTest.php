<?php

/*
 * Test cases for the DBProcessor Class
 * Author: Artem Bobrov
 * Email: <p2547788@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author A Bobrov
 */

namespace DBTesting;

use PHPUnit\Framework\TestCase;

use DB\DBInterface;

class DBClassTest extends TestCase
{
    /*
     * Test DB initialization
     */
    public function testInitDB() {
        $db_interface = new DBInterface;
        $db_interface->initDB();

        $this->assertInstanceOf(DBInterface::class, $db_interface);
    }

    /*
     * Test DB INSERT (via new_log_entry)
     */
    public function testInsertDB() {
        $db_interface = new DBInterface;
        $this->assertTrue($db_interface->new_log_entry(0, 'Testing insert log'));
    }

    /*
     * Test DB SELECT (via get_all_log_entries)
     */
    public function testGetLogEntry() {
        $db_interface = new DBInterface;
        $this->assertIsArray($db_interface->get_all_log_entries());
    }

    /*
     * Test DB SELECT (via get_messages)
     */
    public function testGetMessages() {
        $db_interface = new DBInterface;
        $this->assertIsArray($db_interface->get_messages('+447XXXXXXXX'));
        $this->assertNotEmpty($db_interface->get_messages('+447XXXXXXXX'));
    }
}