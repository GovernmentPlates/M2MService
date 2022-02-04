<?php

/*
 * Test cases for the ValidatorProcessor Class
 * Author: Artem Bobrov
 * Email: <p2547788@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author A Bobrov
 */

namespace ValidatorTesting;

use PHPUnit\Framework\TestCase;
use Validator\ValidatorProcessor;


class ValidatorClassTest extends TestCase
{
    public function testEmailValidation() {
        $validator = new ValidatorProcessor;
        $this->assertIsString($validator->validate_email('pXXXXXXX@my365.dmu.ac.uk'));
        $this->assertFalse($validator->validate_email('value'));
    }

    public function testPhoneNumberValidation() {
        $validator = new ValidatorProcessor;
        $this->assertIsString($validator->validate_mobile_number('+44XXXXXXXX'));
        $this->assertFalse($validator->validate_mobile_number('value'));
    }
}