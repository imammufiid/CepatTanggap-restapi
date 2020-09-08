<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MyResponses
{
    public function __construct()
    {   
        $this->ci =& get_instance();
    }
    function json($status = 200, $message = "", $result = []) {
        $data = [
            "status" => $status,
            "message" => $message,
            "result" => $result
        ];
        header('Content-Type: application/json');
		print_r(json_encode($data, JSON_PRETTY_PRINT));
    }

    public function log($user_id = 0, $api_token = "", $log = "")
    {
        $data = [
            "user_id" => $user_id,
            "user_api_token" =>$api_token,
            "log" => $log,
            "created_at" => date("Y-m-d H:i:s")
        ]; 
        $this->ci->db->insert("log_users", $data);
    }
}