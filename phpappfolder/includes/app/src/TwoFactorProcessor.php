<?php

/*
 * TwoFactorProcessor.php
 *
 * Class that contains functions for two-factor authentication (2fa)
 * Sends codes over SMS
 *
 * Author: Dominic Hollis
 * Email: <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 */

namespace TwoFactorAuth;

include_once("DBInterface.php");
include_once("SMSProcessor.php");

use DB\DBInterface;
use SMS\SMSProcessor;

class TwoFactorProcessor
{
    public function __construct() {}

    public function __destruct() {}

    /*
     * Function to generate a one-time 2FA code (consists of a random 6 character value)
     * @return string
     */
    public function generate_2fa_code()
    {
        $code = "";
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    /*
     * Function to insert a new 2FA code into the database
     * @param user id
     * @param 2fa code
     */
    public function insert_2fa_code($p_user_id, $p_2fa_code)
    {
        $m_db_handle = new DBInterface();
        $m_user_2fa_details = $m_db_handle->get_2fa_code_by_user_id($p_user_id);
        $m_2fa_code_issued_at = time(); //get current time (unix timestamp)
        $m_2fa_code_expiry_at = $m_2fa_code_issued_at + (60 * 10); //set expiry time to 10 minutes from now

        if (empty($m_user_2fa_details))
        {
            $m_db_handle->add_new_2fa_code($p_user_id, $p_2fa_code, $m_2fa_code_issued_at, $m_2fa_code_expiry_at);
        }

        //Use an UPDATE SQL statement instead 2fa user row not empty
        $m_db_handle->update_2fa_code($p_user_id, $p_2fa_code, $m_2fa_code_issued_at, $m_2fa_code_expiry_at);
    }

    /*
     * Function to send a 2FA code to the user via SMS
     * @param user id
     * @param 2fa code
     */
    public function send_2fa_code($phoneNumber, $code)
    {
        $smsProcessor = new SMSProcessor();
        $smsProcessor->send_message($phoneNumber, "[M2MService - 21-3110-AS] Your one-time code is: ".$code.". This code is valid for 10 minutes. Do not share this code.");
    }

    /*
     * Function to check if the 2FA code is valid
     * @param user id
     * @param 2fa code
     * @return boolean
     */
    public function verify_2fa_code($p_user_id, $p_2fa_code)
    {
        $m_db_handle = new DBInterface();
        $m_user_2fa_details = $m_db_handle->get_2fa_code_by_user_id($p_user_id);

        if ($p_2fa_code == $m_user_2fa_details['2faToken'])
        {
            return true;
        }

        return false;
    }
}
