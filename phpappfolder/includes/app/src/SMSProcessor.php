<?php

/*
 * SMSProcessor.php
 *
 * Class to interact with the EE M2M SOAP endpoint, as well as process SMS messages
 *
 * Author: Dominic Hollis, Artem Bobrov
 * Email: <p2533140@my365.dmu.ac.uk> <p2547788@my365.dmu.ac.uk>
 * Date: 17/01/2022
 *
 * @author D Hollis
 * @author A Bobrov
 */

namespace SMS;

include_once('DBInterface.php');
include_once('ValidatorProcessor.php');

use DB\DBInterface;
use Validator\ValidatorProcessor;

class SMSProcessor
{
    private $c_username;
    private $c_password;
    private $c_wsdlEndpoint;

    /*
     * Constructor containing EE M2M Credentials
     */
    public function __construct()
    {
        $this->c_username = 'm2mConnectUsername';
        $this->c_password = 'm2mConnectPassword';
        $this->c_wsdlEndpoint = "https://m2mconnect.ee.co.uk/orange-soap/services/MessageServiceByCountry?wsdl";
    }

    public function __destruct() {}

    /*
     * Send Message function via SOAP call (see p.23 of EE M2M handbook)
     * @param $p_phone_number - phone number (to)
     * @param $p_message - message (string)
     * @return SMSResult|Exception|SoapFault
     */
    function send_message($p_phone_number, $p_message)
    {
        try
        {
            $m_client = new \SoapClient($this->c_wsdlEndpoint);
            $m_options = array(
                'username' => $this->c_username,
                'password' => $this->c_password,
                'deviceMSISDN' => $p_phone_number,
                'message' => $p_message,
                'deliveryReport' => 1,
                'mtBearer' => 'SMS',
            );
            $m_results = $m_client->__soapCall('sendMessage', $m_options);
            return $m_results;
        } catch (\SoapFault $m_err)
        {
            return $m_err;
        }
    }

    /*
     * Read and [m2m gateway] delete message function via SOAP call (see p.33 of EE M2M handbook)
     * @param $p_count - number of messages to read
     * @param $p_device_msisdn - phone number (from)
     * @return SMSMessage|Exception|SoapFault
     */
    function read_and_delete_message($p_count, $p_device_msisdn)
    {
        try
        {
            $m_client = new \SoapClient($this->c_wsdlEndpoint);
            $m_options = array(
                'username' => $this->c_username,
                'password' => $this->c_password,
                'count' => $p_count,
                'deviceMSISDN' => $p_device_msisdn
            );
            $m_results = $m_client->__soapCall('readMessages', $m_options);
            return $m_results;
        } catch (\SoapFault $m_err)
        {
            return $m_err;
        }
    }

    /*
     * Download (via read_and_delete() func), convert the message into XML for easy-access, parse (validates) and store SMS message in DB
     * @param $p_count - number of messages to read
     * @param $p_device_msisdn - phone number (from)
     */
    function parse_messages($p_count, $p_device_msisdn): bool
    {
        $sms_obj = new SMSProcessor;
        $db_obj = new DBInterface;
        $validator = new ValidatorProcessor;

        $sms_array = $sms_obj->read_and_delete_message($p_count, $p_device_msisdn);

       foreach($sms_array as $key)
       {
            $parsed_value = new \SimpleXMLElement($key);

            $srcMSISDN = $parsed_value->sourcemsisdn;
            $destMSISDN = $parsed_value->destinationmsisdn;
            $receivedTime = $parsed_value->receivedtime;
            $bearer = $parsed_value->bearer;
            $messageRef = $parsed_value->messageref;
            $message = $parsed_value->message;

            $message_validated = $validator->validate_incoming_sms($message);
            if($message_validated == 0) {
                return false;
            }

            $db_obj->store_message_in_db($srcMSISDN, $destMSISDN, $receivedTime, $bearer, $messageRef, $message_validated);

            $this->send_message($srcMSISDN, '[M2MApp - 21-3110-AS]['.date('D jS M y H:i:s (e)').'] Message parsed. Instructions saved.');
       }
       return true;
    }
}