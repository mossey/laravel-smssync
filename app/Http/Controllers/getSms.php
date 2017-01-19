<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class getSms extends Controller
{
    //

    /**
     * Gets the messages(SMSs) sent by SMSsync as a POST request.
     *
     */
    public function index(Request $request){
        if($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            if(isset($_GET['task']) AND $_GET['task'] === 'result'){
                return $this->get_sms_delivery_report();
            }
            else if( isset($_GET['task']) && $_GET['task'] === 'sent')
            {
                return $this->get_sent_message_uuids();
            }
            else
            {
                return $this->get_message($request);
            }
        }
        else
        {
            return $this->send_task()->send_messages_uuids_for_sms_delivery_report();
        }
    }

    function get_message($request)
    {
        $error = NULL;
        // Set success to false as the default success status
        $success = false;
        /**
         *  Get the phone number that sent the SMS.
         */
        if (isset($request->from))
        {
            $from = $request->from;
        }
        else
        {
            $error = 'The from variable was not set';
        }
        /**
         * Get the SMS aka the message sent.
         */
        if (isset($request->message))
        {
            $message = $request->message;
        }
        else
        {
            $error = 'The message variable was not set';
        }
        /**
         * Get the secret key set on SMSsync side
         * for matching on the server side.
         */
        if (isset($request->secret))
        {
            $secret = $request->from;
        }
        /**
         * Get the timestamp of the SMS
         */
        if(isset($request->sent_timestamp))
        {
            $sent_timestamp = $request->sent_timestamp;
        }
        /**
         * Get the phone number of the device SMSsync is
         * installed on.
         */
        if (isset($request->sent_to))
        {
            $sent_to = $request->sent_to;
        }
        /**
         * Get the unique message id
         */
        if (isset($request->message_id))
        {
            $message_id = $request->message_id;
        }
        /**
         * Get device ID
         */
        if (isset($request->device_id))
        {
            $device_id = $request->device_id;
        }
        /**
         * Now we have retrieved the data sent over by SMSsync
         * via HTTP. Next thing to do is to do something with
         * the data. Either echo it or write it to a file or even
         * store it in a database. This is entirely up to you.
         * After, return a JSON string back to SMSsync to know
         * if the web service received the message successfully or not.
         *
         * In this demo, we are just going to save the data
         * received into a text file.
         *
         */
        if ((strlen($from) > 0) AND (strlen($message) > 0) AND
            (strlen($sent_timestamp) > 0 )
            AND (strlen($message_id) > 0))
        {
            /* The screte key set here is 123456. Make sure you enter
             * that on SMSsync.
             */
            if ( ( $secret == '123456'))
            {
                $success = true;
            } else
            {
                $error = "The secret value sent from the device does not match the one on the server";
            }
            // now let's write the info sent by SMSsync
            //to a file called test.txt
            $string = "From: ".$from."\n";
            $string .= "Message: ".$message."\n";
            $string .= "Timestamp: ".$sent_timestamp."\n";
            $string .= "Messages Id:" .$message_id."\n";
            $string .= "Sent to: ".$sent_to."\n";
            $string .= "Device ID: ".$device_id."\n\n\n";
            self::write_message_to_file($string);
        }
        /**
         * Comment the code below out if you want to send an instant
         * reply as SMS to the user.
         *
         * This feature requires the "Get reply from server" checked on SMSsync.
         */
        self::send_instant_message($from);
        /**
         * Now send a JSON formatted string to SMSsync to
         * acknowledge that the web service received the message
         */
        $response = json_encode([
            "payload"=> [
                "success"=>$success,
                "error" => $error
            ]
        ]);
        //send_response($response);
    }
    /**
     * Writes the received responses to a file. This acts as a database.
     */
    function write_message_to_file($message)
    {
        $myFile = "test.txt";
        $fh = fopen($myFile, 'a') or die("can't open file");
        @fwrite($fh, $message);
        @fclose($fh);
    }
    /**
     * Implements the task feature. Sends messages to SMSsync to be sent as
     * SMS to users.
     */
    function send_task()
    {
        /**
         * Comment the code below out if you want to send an instant
         * reply as SMS to the user.
         *
         * This feature requires the "Get reply from server" checked on SMSsync.
         */
        if (isset($_GET['task']) AND $_GET['task'] === 'send')
        {
            $m = "Sample Task Message";
            $f = "+000-000-0000";
            $s = "true";
            $reply[0] = [
                "to" => $f,
                "message" => $m,
                "uuid" => "1ba368bd-c467-4374-bf28"
            ];
            // Send JSON response back to SMSsync
            $response = json_encode(
                ["payload"=>[
                    "success"=>$s,
                    "task"=>"send",
                    "secret" => "123456",
                    "messages"=>array_values($reply)]
                ]);
            send_response($response);
        }
    }
    /**
     * This sends an instant response when the server receive messages(SMSs) from
     * SMSsync. This requires the settings "Get Reply from Server" enabled on
     * SMSsync.
     */
    function send_instant_message($to)
    {
        $m = "Your message has been received";
        $f = "+000-000-0000";
        $s = true;
        $reply[0] = [
            "to" => $to,
            "message" => $m,
            "uuid" => "1ba368bd-c467-4374-bf28"
        ];
        // Send JSON response back to SMSsync
        $response = json_encode(
            ["payload"=>[
                "success"=>$s,
                "task"=>"send",
                "secret" => "123456",
                "messages"=>array_values($reply)]
            ]);
        self::send_response($response);
    }
    function send_response($response)
    {
        // Avoid caching
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Content-type: application/json; charset=utf-8");
        echo $response;
    }
    function get_sent_message_uuids()
    {
        $data = file_get_contents('php://input');
        $queued_messages = file_get_contents('php://input');
        // Writing this to a file for demo purposes.
        // In production, you will have to process the JSON string
        // and remove the messages from the database or where ever the
        // messages are stored so the next Task run, the server won't add
        // these messages.
        write_message_to_file($queued_messages."\n\n");
        send_message_uuids_waiting_for_a_delivery_report($queued_messages);
    }
    /**
     * Sends message UUIDS to SMSsync for their sms delivery status report.
     * When SMSsync send messages from the server as SMS to phone numbers, SMSsync
     * can send back status delivery report for these messages.
     */
    function send_message_uuids_waiting_for_a_delivery_report($queued_messages)
    {
        // Send back the received messages UUIDs back to SMSsync
        $json_obj = json_decode($queued_messages);
        $response = json_encode(
            [
                "message_uuids"=>$json_obj->queued_messages
            ]);
        send_response($response);
    }
    function send_messages_uuids_for_sms_delivery_report()
    {
        if(isset($_GET['task']) AND $_GET['task'] == 'result'){
            $response = json_encode(
                [
                    "message_uuids" => ['1ba368bd-c467-4374-bf28']
                ]);
            send_response($response);
        }
    }
    /**
     * Get status delivery report on sent messages
     *
     */
    function get_sms_delivery_report()
    {
        if($_GET['task'] === 'result' AND $_GET['secret']=== '123456')
        {
            $message_results = file_get_contents('php://input');
            write_message_to_file("message ".$message_results."\n\n");
        }
    }
}