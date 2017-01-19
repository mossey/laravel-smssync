<?php

namespace App\Http\Controllers;

use Request;
use App\texts;
class getSms extends Controller
{
    /**
     * Gets the messages(SMSs) sent by SMSsync as a POST request.
     *
     */
    public function index(){

        if($_SERVER['REQUEST_METHOD'] === 'POST')
        {
          return "sawa";
        }
        /**
         * Comment the code below out if you want to send an instant
         * reply as SMS to the user.
         *
         * This feature requires the "Get reply from server" checked on SMSsync.
         */
        send_instant_message($from);
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
        send_response($response);
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

// Execute functions above



    /*
     * public function index(){
        $phone=Request::get('phone');
        $message=Request::get('message');
        $message_id=Request::get('message_id');
        $sent_to=Request::get('sent_to');
        $secret=Request::get('secret');
        $device_id=Request::get('device_id');
        $sent_timestamp=Request::get('sent_timestamp');

        $Texts =new texts();
        $Texts->phone=$phone;
        $Texts->message=$message;
        $Texts->message_id=$message_id;
        $Texts->sent_to=$sent_to;
        $Texts->secret=$secret;
        $Texts->device_id=$device_id;
        $Texts->sent_timestamp=$sent_timestamp;
        $Texts->save();

        return "saved";

    }
    public function showSms(){
        $res=texts::all();
        return json_encode($res);
    }*/
    //







}
