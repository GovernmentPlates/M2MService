<?php

/*
 * ValidatorProcessor.php
 *
 * Class to validate data (i.e. incoming data, user inputs etc.)
 *
 * Author: Artem Bobrov, Dominic Hollis
 * Email: <p2547788@my365.dmu.ac.uk> <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author A Bobrov
 * @author D Hollis
 */

namespace Validator;

class ValidatorProcessor
{
    public function __construct() {}

    public function __destruct() {}

    /*
     * Validate incoming SMS function - used to check if the SMS message conforms to our style
     * @param SMS Message
     * @return mixed
     */
    public function validate_incoming_sms($p_message)
    {
        $p_message = $this->sanitize_string($p_message);
        $m_binary_expected = substr($p_message, 0, 8);
        $m_temperature = substr($p_message,8, 2);
        $m_last_keypad_value = substr($p_message, 10,1);
        $m_team_name = substr($p_message, 11, 10);

        if (strspn($m_binary_expected, '01'))
        {
            if($m_temperature >= 10 && $m_temperature <= 99)
            {
                if(is_numeric($m_last_keypad_value)) {
                    if($m_team_name == '21-3110-AS') {
                        return $p_message;
                    }
                }
            }
        }
        return 0;
    }

    /*
     * Function to sanitize filter and validate a dmu.ac.uk email address
     * @param Email address
     * @return mixed
     */
    public function validate_email($p_email)
    {
        $m_user_email_sanitized = filter_var($p_email, FILTER_SANITIZE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
        $m_user_validated_email = filter_var($m_user_email_sanitized, FILTER_VALIDATE_EMAIL);

        # If email domain does not contain 'dmu.ac.uk', return false
        if(!strpos($m_user_validated_email, 'dmu.ac.uk'))
        {
            return false;
        }

        return $m_user_email_sanitized;
    }

    /*
     * Function to sanitize a string
     * @param String
     * @return mixed
     */
    public function sanitize_string($p_string)
    {
        return filter_var($p_string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    /*
     * Function to validate a UK mobile phone number
     * @param Mobile number
     * @return boolean
     */
    public function validate_mobile_number($p_mobile_number)
    {
        $m_test_regex = '/^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$/';
        $m_test_mobile_number = preg_match($m_test_regex, $p_mobile_number);

        if ($m_test_mobile_number)
        {
            return $p_mobile_number;
        }

        return false;

    }
}