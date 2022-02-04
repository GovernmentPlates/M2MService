<?php
/*
 * DBInterface.php
 *
 * Class to interact with the MariaDB server
 *
 * Author: Dominic Hollis, Artem Bobrov, Alex Grosvenor
 * Email: <p2533140@my365.dmu.ac.uk> <p2547788@my365.dmu.ac.uk> <p2573368@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 * @author A Bobrov
 * @author A Grosvenor
 */

namespace DB;

include_once("SMSProcessor.php");

class DBInterface
{
    private $c_db_host;
    private $c_db_port;
    private $c_db_user;
    private $c_db_pass;
    private $c_db_name;
    private $c_team_id;

    public function __construct()
    {
        $this->c_db_host = 'localhost';
        $this->c_db_port = '3306';
        $this->c_db_user = 'user';
        $this->c_db_pass = 'pass';
        $this->c_db_name = 'm2mapp';
        $this->c_db_status_messages = array();
        $this->c_team_id = '21-3110-AS';
    }

    public function __destruct() {}

    /*
     * Error handler function (used for debugging) - either dumps the error in debug mode or logs it (and sends an SMS) in production mode
     * @param $p_exception
     * @return Error message (with exception)
     */
    private function error_handler($p_exception)
    {
        $c_sms_handle = new \SMS\SMSProcessor;
        // if debug is enabled, display error message
        if (ini_get('display_errors')) {
            echo "Error - ".$p_exception." Trace: ".var_dump(debug_backtrace());
            print('<div class="alert alert-danger"><b>[!] DBInterface Error - Caught Exception:</b><br/><pre>'.$p_exception.'</pre><br/><hr><b>Stack Trace:</b><pre>'.var_dump(debug_backtrace()).'</pre></div>');
        } else {
            //Return an HTTP 500 error and send a SMS message on DB failure in prod
            header("HTTP/1.0 500 Internal Server Error");
            $c_sms_handle->send_message('+44XXXXXXXXX', '[M2MApp - 21-3110-AS]['.date('D jS M y H:i:s (e)').'] EMERGENCY: Critical error occurred in src/DBInterface.php.');
        }
    }

    /*
     * Function to initialize the connection to the database
     * @return PDO object
     */
    public function initDB() {
        try {
            $dsn = 'mysql:host='.$this->c_db_host.';port='.$this->c_db_port.';dbname='.$this->c_db_name;
            $db = new \PDO($dsn, $this->c_db_user, $this->c_db_pass);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->c_db_status_messages['initDB'] = 'DB connection established.';
            return $db;

        } catch (\PDOException $e)
        {
            $this->c_db_status_messages['initDB'] = 'DB connection failed.';
            $this->error_handler($e);
            return 0;
        }
    }

    /*
     * Function to get connection messages
     * @return array
     */
    public function get_db_status_messages()
    {
        return $this->c_db_status_messages;
    }

    /*
     * Function to store a log entry in the 'log' table of the DB
     * @param Log Entry
     * @param user id (if applicable)
     * @param Log message
     */
    public function new_log_entry($p_log_type, $p_log_message): bool
    {
        try {
            $db_interface = new DBInterface;
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("INSERT INTO log (logType, logMsg) VALUES (:logType, :logMsg)");
            $stmt->bindParam(':logType', $p_log_type);
            $stmt->bindParam(':logMsg', $p_log_message);
            $stmt->execute();
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
            return false;
        }
        return true;
    }

    /*
     * Function to get all log entries from the 'log' table of the DB
     * @return array
     */
    public function get_all_log_entries()
    {
        try {
            $db_interface = new DBInterface;
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("SELECT * FROM log ORDER BY log_id DESC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e)
        {
            $this->error_handler($e);
            return 0;
        }
    }

    /*
     * Function to store a validated message in the DB
     * @param source MSISDN (from), destination MSISDN (to/M2M phone number), received datetime stamp, bearer (SMS), message reference, message (validated)
     * @return mixed
     */
    public function store_message_in_db($p_srcMSISDN, $p_destMSISDN, $p_receivedDate, $p_bearer, $p_messageRef, $message_validated)
    {
        $success = false;
        try {
            $m_db_interface = new DBInterface;
            $m_db_object = $m_db_interface->initDB();
            $stmt = $m_db_object->prepare("INSERT INTO messages (srcMSISDN, destMSISDN, receivedDate, teamID,
                      bearer, messageRef, switch1, switch2, switch3, switch4, fanEnabled, fanForward, fanReverse, 
                      heaterEnabled, heaterTemperature, keypadLastValue) VALUES (:srcMSISDN, :destMSISDN, :receivedDate,
                                                                                 :teamID, :bearer, :messageRef, :switch1,
                                                                                 :switch2, :switch3, :switch4, :fanEnabled,
                                                                                 :fanForward, :fanReverse, :heaterEnabled,
                                                                                 :heaterTemperature, :keypadLastValue)");

            $switch1 = substr($message_validated, 0, 1);
            $switch2 = substr($message_validated, 1, 1);
            $switch3 = substr($message_validated, 2, 1);
            $switch4 = substr($message_validated, 3, 1);
            $fanEnabled = substr($message_validated, 4, 1);
            $fanForward = substr($message_validated, 5, 1);
            $fanReverse = substr($message_validated, 6, 1);
            $heaterEnabled = substr($message_validated, 7, 1);
            $heaterTemperature = substr($message_validated, 8, 2);
            $keypadLastValue = substr($message_validated, 10, 1);


            $stmt->bindParam(':srcMSISDN', $p_srcMSISDN);
            $stmt->bindParam(':destMSISDN', $p_destMSISDN);
            $stmt->bindParam(':teamID', $this->c_team_id);
            $stmt->bindParam(':bearer', $p_bearer);
            $stmt->bindParam(':messageRef', $p_messageRef);
            $stmt->bindParam(':receivedDate', $p_receivedDate);
            $stmt->bindParam(':switch1', $switch1);
            $stmt->bindParam(':switch2', $switch2);
            $stmt->bindParam(':switch3', $switch3);
            $stmt->bindParam(':switch4', $switch4);
            $stmt->bindParam(':fanEnabled', $fanEnabled);
            $stmt->bindParam(':fanForward', $fanForward);
            $stmt->bindParam(':fanReverse', $fanReverse);
            $stmt->bindParam(':heaterEnabled', $heaterEnabled);
            $stmt->bindParam(':heaterTemperature', $heaterTemperature);
            $stmt->bindParam(':keypadLastValue', $keypadLastValue);

            $stmt->execute();

            $success = true;

        } catch(\PDOException $e)
        {
            $this->error_handler($e);
        }

        if ($success)
        {
            return true;
        }
    }

    /*
     * Function that returns a specific array of instructions (depending on param) from the DB
     * @param Senders phone number to find
     * @return Array
     */
    public function get_messages($srcMSISDN) {
        $srcMSISDN = str_replace('+', '', $srcMSISDN);
        $db_interface = new DBInterface;
        $db_object = $db_interface->initDB();
        $stmt = $db_object->prepare("SELECT * FROM messages WHERE srcMSISDN = :srcMSISDN ORDER BY msg_id DESC");
        $stmt->bindParam(':srcMSISDN', $srcMSISDN);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /*
     * Function to get the last very last record (message) from the messages DB table - returns an array
     * @return Array
     */
    public function get_last_message()
    {
        $db_interface = new DBInterface;
        $db_object = $db_interface->initDB();
        $stmt = $db_object->prepare("SELECT * FROM messages WHERE msg_id=(SELECT max(msg_id) FROM messages)");
        $stmt->execute();
        $m_msg_row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($stmt->rowCount() == 1)
        {
            return $m_msg_row;
        }
    }

    /*
     * Function that adds new users details to the users table of the DB - used within the UserProcessor class
     * @param Username
     * @param E-mail address
     * @param Phone Number
     * @param Password
     */
    public function add_user_to_db($p_username, $p_email, $p_phone, $p_password)
    {
        try
        {
            $m_db_interface = new DBInterface();
            $db_object = $m_db_interface->initDB();
            $stmt = $db_object->prepare("INSERT INTO users (username, email, phone, password) VALUES (:username, :email, :phone, :password)");
            $stmt->bindParam(':username', $p_username);
            $stmt->bindParam(':email', $p_email);
            $stmt->bindParam(':phone', $p_phone);
            $stmt->bindParam(':password', $p_password);
            $stmt->execute();
            $this->new_log_entry("1", "New User Registered $p_username, $p_email ($p_phone)");
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /*
     * Retrieve user details from the database (via email address) and send it back as an array
     * @param E-mail address
     * @return Array
     */
    public function retrieve_user($p_email)
    {
        try
        {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $p_email);
            $stmt->execute(array(":email"=>$p_email));
            $m_user_row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 1)
            {
                return $m_user_row;
            }
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }


    /*
     * Edit user password
     * @param user hashed password (there is NO validation at this end - use generate_secure_hash from UserProcessor)
     * @param user ID (obtained usually from either session or other method)
     * @return true|PDOException
     */
    public function edit_user_db_password($p_user_hashed_pass, $p_user_id)
    {
        try
        {
            $m_db_interface = new DBInterface();
            $m_db_object = $m_db_interface->initDB();
            $stmt = $m_db_object->prepare("UPDATE users SET password=:hashed_pass WHERE u_id=:user_id");
            $stmt->bindParam(':hashed_pass', $p_user_hashed_pass);
            $stmt->bindParam(':user_id', $p_user_id);
            $stmt->execute();

            return true;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }


    /*
     * Function to retrieve user details (as an array) by ID
     * @param User ID
     * @return Array
     */
    public function retrieve_user_by_id($p_user_id)
    {
        try
        {
            $m_db_interface = new DBInterface();
            $m_db_object = $m_db_interface->initDB();
            $stmt = $m_db_object->prepare("SELECT * FROM users WHERE u_id = :user_id");
            $stmt->bindParam(':user_id', $p_user_id);
            $stmt->execute();
            $m_user_row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 1)
            {
                return $m_user_row;
            }

            return false;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /*
     * Function to get all stored heater temperature information from the stored messages DB
     * @return Array with heater values
     */
    public function get_all_temp()
    {
        try
        {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("SELECT heaterTemperature FROM messages");
            $stmt->execute();
            $m_heater_values = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $m_heater_array = array();

            foreach($m_heater_values as $key)
            {
                array_push($m_heater_array, $key['heaterTemperature']);
            }

            return $m_heater_array;

        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /*
     * Function to get all stored received message dates
     * @return Array with SQL-formatted date and time stamp values
     */
    public function get_all_message_received_dates()
    {
        try
        {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("SELECT receivedDate FROM messages");
            $stmt->execute();
            $receivedDateValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $dateArray = array();
            foreach($receivedDateValues as $key)
            {
                array_push($dateArray, $key['receivedDate']);
            }
            return $dateArray;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /* Function to insert a 2fa code into the database
     * @param user ID
     * @param 2fa code
     * @param issue at (timestamp)
     * @param valid until (timestamp)
     * @return true|PDOException
     */
    public function add_new_2fa_code($p_user_id, $p_2fa_token, $p_issued_at, $p_valid_until)
    {
        try {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("INSERT INTO 2fa (user_id, 2faToken, issuedAt, validTill) VALUES (:user_id, :2fa_token, :issued_at, :valid_until)");
            $stmt->bindParam(':user_id', $p_user_id);
            $stmt->bindParam(':2fa_token', $p_2fa_token);
            $stmt->bindParam(':issued_at', $p_issued_at);
            $stmt->bindParam(':valid_until', $p_valid_until);
            $stmt->execute();

            return true;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /*
     * Function to update the 2fa code in the database by user_id
     * @param user ID
     * @param 2fa code
     * @param issue at (timestamp)
     * @param valid until (timestamp)
     * @return true|PDOException
     */
    public function update_2fa_code($p_user_id, $p_2fa_token, $p_issued_at, $p_valid_until)
    {
        try {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("UPDATE 2fa SET 2faToken = :2fa_token, issuedAt = :issued_at, validTill = :valid_until WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $p_user_id);
            $stmt->bindParam(':2fa_token', $p_2fa_token);
            $stmt->bindParam(':issued_at', $p_issued_at);
            $stmt->bindParam(':valid_until', $p_valid_until);
            $stmt->execute();

            return true;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

    /*
     * Function to retrieve 2fa code, issued at and valid until from the database by user ID
     * @param user ID
     * @return Array with 2fa code, issued at and valid until
     */
    public function get_2fa_code_by_user_id($p_user_id)
    {
        try {
            $db_interface = new DBInterface();
            $db_object = $db_interface->initDB();
            $stmt = $db_object->prepare("SELECT 2faToken, issuedAt, validTill FROM 2fa WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $p_user_id);
            $stmt->execute();
            $m_2fa_code = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($stmt->rowCount() == 1)
            {
                return $m_2fa_code;
            }

            return false;
        } catch (\PDOException $e)
        {
            $this->error_handler($e);
        }
    }

}