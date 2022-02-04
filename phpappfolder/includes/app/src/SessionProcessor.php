<?php

/*
 * SessionProcessor.php
 *
 * Class that contains functions to create, get and delete session values
 *
 * Author: Dominic Hollis
 * Email: <p2533140@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 */

namespace Session;

class SessionProcessor
{

    public function __construct() {}

    public function __destruct() {}

    /*
     * Function to create a session token
     * @param $p_session_token - name of the session token
     * @param $p_session_var - data to be stored in the session token
     * @return bool - true if session token created, false otherwise
     */
    public function set_session_token($p_session_token, $p_session_var)
    {
        $session_value_set_successfully = false;
        if (!empty($p_session_var)) {
            $_SESSION[$p_session_token] = $p_session_var;
            if (strcmp($_SESSION[$p_session_token], $p_session_var) == 0) {
                $session_value_set_successfully = true;
            }
        }
        return $session_value_set_successfully;
    }

    /*
     * Function to get a session token
     * @param $p_session_token - name of the session token
     * @return mixed - session token value if session token exists, null otherwise
     */
    public function get_session_token($p_session_token)
    {
        $m_session_value = null;

        if (isset($_SESSION[$p_session_token]))
        {
            $m_session_value = $_SESSION[$p_session_token];
        }

        return $m_session_value;
    }

    /*
     * Function to delete a session token
     * @param $p_session_token - name of the session token
     * @return bool - true if session token deleted, false otherwise
     */
    public function delete_session_token($p_session_token): bool
    {
        if (isset($_SESSION[$p_session_token]))
        {
            unset($_SESSION[$p_session_token]);
            return true;
        }

        return false;
    }

    /*
     * Function to check if a session token exists
     * @param $p_session_token - name of the session token
     * @return bool - true if session token exists, false otherwise
     */
    public function check_session_exists($p_session_token): bool
    {
        if ($this->get_session_token($p_session_token) != null)
        {
            return true;
        }

        return false;
    }
}