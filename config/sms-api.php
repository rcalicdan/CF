<?php

return [

    'country_code' => '91', // Country code to be added
    'default' => env('SMS_API_DEFAULT_GATEWAY', 'gateway_name_basic'), // Choose default gateway

    //    Basic Gateway Sample Configuration
    'gateway_name_basic' => [
        'method' => 'GET', // Choose Request Method (GET/POST) Default:GET
        'url' => 'BaseUrl', // Base URL
        'params' => [
            'send_to_param_name' => '', // Send to Parameter Name
            'msg_param_name' => '', // Message Parameter Name
            'others' => [
                'param1' => '',
                'param2' => '',
                'param3' => '',
                // More params can be added
            ],
        ],
        'headers' => [
            'header1' => '',
            'header2' => '',
            // More headers can be added
        ],
        'add_code' => true, // Include Country Code (true/false)
    ],

    //    Advanced Gateway Sample Configuration
    'gateway_name_advanced' => [
        'method' => 'POST', // Choose Request Method (GET/POST) Default:GET
        'url' => 'BaseUrl', // Base URL
        'params' => [
            'send_to_param_name' => '', // Send to Parameter Name
            'msg_param_name' => '', // Message Parameter Name
            'others' => [
                'param1' => '',
                'param2' => '',
                'param3' => '',
                // More params can be added
            ],
        ],
        'headers' => [
            'header1' => '',
            'header2' => '',
            // More headers can be added
        ],
        'json' => true, // OPTIONAL: Use if you want the params to be sent in JSON format instead of query params (accepts true/false)
        //        'jsonToArray' => false, // OPTIONAL, use only if you want "to" param as text instead of array in JSON payload.

        //        'wrapper' => 'wrapper_name', // OPTIONAL: Use only if you want the JSON request to be wrapped (accepts wrapper name)
        //        'wrapperParams' => [
        //            'wrapperParam1' => '', //Wrapper Param
        //        ],
        /** Learn more about wrapper here: https://github.com/gr8shivam/laravel-sms-api#wrapper-parameter **/
        'add_code' => true, // Include Country Code (true/false)
    ],

    /*
    *
    * Examples :-
    *
    */

    //    SMSNIX Sample Config
    'smsnix' => [
        'url' => 'http://bulk.smsnix.in/vendorsms/pushsms.aspx?',
        'params' => [
            'send_to_param_name' => 'msisdn', // Send to Parameter Name
            'msg_param_name' => 'msg', // Message Parameter Name
            'others' => [
                'user' => '', // Your username
                'password' => '', // Your Password
                'sid' => '', // Your Sender ID
                'fl' => '0',
                'gwid' => '2',
            ],
        ],
        'add_code' => true, // Include Country Code
    ],

    //    MSG91 Sample Config
    'msg91' => [
        'method' => 'POST', // Choose Request Method (GET/POST)
        'url' => 'https://control.msg91.com/api/v2/sendsms?', // Base URL
        'params' => [
            'send_to_param_name' => 'to', // Send to Parameter Name
            'msg_param_name' => 'message', // Message Parameter Name
            'others' => [
                'authkey' => '', // Your auth key
                'sender' => '', // Your Sender ID
                'route' => '4',
                'country' => '91',
            ],
        ],
        'json' => true, // Use if you want the params to be sent in JSON format instead of query params
        'wrapper' => 'sms', // Optional, use only if you want the JSON request to be wrapped
        'add_code' => false, // Include Country Code (true/false)
    ],

    'smsapi_linkmobility' => [
        'method' => 'POST',
        'url' => 'https://api.smsapi.pl/sms.do',
        'params' => [
            'send_to_param_name' => 'to',
            'msg_param_name' => 'message',
            'others' => [
                'from' => env('SMS_API_FROM_NUMBER'),
                'format' => 'json',
                'encoding' => 'utf-8',
                'test' => env('APP_ENV') === 'production' ? '0' : '1', // Set to '0' for production
            ],
        ],
        'headers' => [
            'Authorization' => 'Bearer '.env('SMS_API_AUTH_TOKEN'),
            'Content-Type' => 'application/json',
        ],
        'json' => true, // Ensure JSON format for request payload
        'add_code' => false, // Include country code in recipient number
    ],

    'twilio' => [
        'method' => 'POST',
        'url' => 'https://api.twilio.com/2010-04-01/Accounts/'.env('TWILIO_ACCOUNT_SID').'/Messages.json',
        'params' => [
            'send_to_param_name' => 'To', // Recipient phone number
            'msg_param_name' => 'Body',  // Message body
            'others' => [
                'From' => env('TWILIO_FROM_NUMBER'), // Your Twilio phone number
            ],
        ],
        'headers' => [
            'Authorization' => 'Basic '.base64_encode(env('TWILIO_ACCOUNT_SID').':'.env('TWILIO_AUTH_TOKEN')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
        'json' => false, // Twilio expects form-urlencoded data
        'add_code' => false, // Include country code in the recipient number
    ],

];
