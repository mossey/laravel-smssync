<?php

namespace App\Http\Controllers;

use Request;
use App\texts;
class getSms extends Controller
{
    public function getSms(){
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
    //
}
