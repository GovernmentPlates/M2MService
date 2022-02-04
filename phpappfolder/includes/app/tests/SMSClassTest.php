<?php

/*
 * Test cases for the SMSProcessor Class
 * Author: Artem Bobrov
 * Email: <p2547788@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author A Bobrov
 */

namespace SMSTesting;

use PHPUnit\Framework\TestCase;

use Validator\ValidatorProcessor;
use SMS\SMSProcessor;

class SMSClassTest extends TestCase
{
    /*
     * Validation test sets
     */
    public function testSMSValidation() {
        $test_array_true = [
            0 => '0011011155321-3110-AS',
            1 => '1101010199121-3110-AS',
            2 => '0101010139821-3110-AS'
        ];
        $test_array_false = [
            0 => 'fake-string',
            1 => 213213213123123213,
            2 => 'adasda',
            3 =>'DROP DATABASE m2mapp'
        ];

        $validator = new ValidatorProcessor;

        foreach($test_array_true as $element) {
            $this->assertIsString($validator->validate_incoming_sms($element));
        }
        foreach($test_array_false as $element) {
            $this->assertSame($validator->validate_incoming_sms($element), 0);
        }
    }

    // Will insert new messages only (unread)
    /*
     * Parse function testing
     */
    public function testMessageParsing() {
        $sms = new SMSProcessor;
        $this->assertTrue($sms->parse_messages(10, '+44XXXXXXXX'));
    }
}