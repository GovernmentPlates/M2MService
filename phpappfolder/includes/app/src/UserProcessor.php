<?php

/*
 * UserProcessor.php
 *
 * Class that contains user-associated functions (i.e. creating accounts, checking passwords etc.)
 *
 * Author: Dominic Hollis
 * Email: <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 */

namespace User;

include_once("ValidatorProcessor.php");
include_once("DBInterface.php");
include_once("SessionProcessor.php");

use DB\DBInterface;
use SMS\SMSProcessor;
use Validator\ValidatorProcessor;
use Session\SessionProcessor;

class UserProcessor
{
    public function __construct() {}

    public function __destruct() {}

    /*
     * Function to generate a secure password hash
     * @param $password - the password to hash
     * @return string - the hashed password
     */
    public function generate_secure_hash($p_password)
    {
        $m_options = [
            'cost' => PASSWORD_BCRYPT_DEFAULT_COST
        ];

        return password_hash($p_password, PASSWORD_BCRYPT, $m_options);
    }

    /*
     * Function to verify a password hash
     * @param $p_candidate_string - candidate password to verify
     * @param $p_hashed_string - hashed password to verify against
     * @return bool - true if the candidate password matches and the hashed password, false otherwise
     */
    public function verify_secure_hash($p_candidate_string, $p_hashed_string): bool
    {
        if (password_verify($p_candidate_string, $p_hashed_string))
        {
            return true;
        }

        return false;
    }

    /*
     * Function to validate a new users details (uses ValidatorProcessor), as well as generate a secure hash said user
     * @param $p_username - input username
     * @param $p_email - the email to validate
     * @param $p_phone - input phone number
     * @param $p_password - input (un-encoded) password
     * @return mixed - array of details if email and phone are valid, otherwise false
     */
    public function register_new_user($p_username, $p_email, $p_phone, $p_password)
    {
        $m_db_handle = new DBInterface();

        $m_validator_handle = new ValidatorProcessor();
        $m_validated_username = $m_validator_handle->sanitize_string($p_username);
        $m_validated_email = $m_validator_handle->validate_email($p_email);
        $m_validated_phone = $m_validator_handle->validate_mobile_number($p_phone);
        $m_validated_password = $this->generate_secure_hash($p_password);

        $m_db_user_exists = $m_db_handle->retrieve_user($m_validated_email);

        if (!empty($m_db_user_exists['email']))
        {
            return false;
        }

        if(empty($m_validated_email) or empty($m_validated_phone))
        {
            return false;
        }

        $m_user_details = [
            'username' => $m_validated_username,
            'email' => $m_validated_email,
            'phone' => $m_validated_phone,
            'password' => $m_validated_password
        ];

        $m_db_handle->add_user_to_db($m_user_details['username'], $m_user_details['email'], $m_user_details['phone'], $m_user_details['password']);

        return true;
    }

    /*
     * Function to authenticate (log-in) an existing user
     * @param $p_username - Username
     * @param $p_password - (Un-hased/raw) password
     * @return mixed
     */
    public function authenticate_user($p_email, $p_password)
    {
        $m_db_handle = new DBInterface();
        $m_validator_handle = new ValidatorProcessor();

        $m_validated_email = $m_validator_handle->validate_email($p_email);
        $m_sanitized_password = $m_validator_handle->sanitize_string($p_password);

        $m_user_details = $m_db_handle->retrieve_user($m_validated_email);

        if (empty($m_user_details))
        {
            return false;
        }

        if ($this->verify_secure_hash($m_sanitized_password, $m_user_details['password']))
        {
            $m_2fa_session_handle = new SessionProcessor();
            //Create a 2FA session - this session token is deleted after successful 2FA verification
            $m_2fa_session_handle->set_session_token('2fa_session', $m_user_details['u_id']);
            return true;
        } else
        {
            return false;
        }
    }

    /*
     * Function to change an existing users' password (used from within the settings route/page)
     * @param Given user password
     * @param Current user (hashed) password - stored on DB
     * @param New user password
     * @param New user password (confirmation)
     * @param User ID (from users table)
     * @return bool
     */
    public function change_user_password($p_given_current_user_db_password, $p_current_user_db_password, $p_new_password, $p_confirm_new_password, $p_user_id)
    {
        $m_validator_handle = new ValidatorProcessor();
        $m_db_object_handle = new DBInterface();

        $m_santizied_given_pass = $m_validator_handle->sanitize_string($p_given_current_user_db_password);
        $m_santizied_new_pass = $m_validator_handle->sanitize_string($p_new_password);
        $m_santizied_confirm_new_pass = $m_validator_handle->sanitize_string($p_confirm_new_password);

        $m_hash_verify = $this->verify_secure_hash($m_santizied_given_pass, $p_current_user_db_password);

        if (!$m_hash_verify)
        {
            return false;
        }

        if (!($m_santizied_new_pass == $m_santizied_confirm_new_pass))
        {
            return false;
        }

        $m_user_id = $m_db_object_handle->retrieve_user_by_id($p_user_id);

        if (empty($m_user_id['u_id']))
        {
            return false;
        }

        $m_new_hashed_password = $this->generate_secure_hash($m_santizied_new_pass);
        $m_db_object_handle->edit_user_db_password($m_new_hashed_password, $p_user_id);
        return true;
    }
}