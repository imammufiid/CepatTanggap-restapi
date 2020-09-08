<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DialNumber extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dialNumber()
    {
        $status = 200;
        $message = "";
        $result = [];

        $api_key = $this->input->post("api_key");

        if (empty($api_key)) {
            $status = 500;
            $message = "Cannot Request";
        } else {
            $result = $this->db->get("dial_number")->result();
            if ($result) {
                $message = "Data dial number";
            }
        }

        $this->myresponses->json($status, $message, $result);
    }

    public function create_dialNumber()
    {
        $api_key = $this->input->post("api_key");
        $nama = $this->input->post("nama");
        $number = $this->input->post("number");
        $status_dialnumber = $this->input->post("status");
        $result = [];
        $status = 200;
        $message = "";

        if (empty($api_key)) {
            $status = 500;
            $message = "Cannot Request";
        } else {
            // checking status
            if (empty($status_dialnumber)) {
                $status_dialnumber = 0;
            }

            if (empty($nama) || empty($number)) {
                $status = 400;
                $message = "Form tidak boleh kosong!";
            } else {
                $data = [
                    "nama_dialnumber" => $nama,
                    "number" => $number,
                    "status" => $status_dialnumber,
                    "created_at" => date("Y-m-d H:i:s")
                ];

                if ($this->db->insert("dial_number", $data)) {
                    $status = 201;
                    $message = "Berhasil menambahkan dial number...";
                    $insert_id = $this->db->insert_id();
                } else {
                    $status = 400;
                    $message = "Gagal menambahkan dial number...";
                }
            }
        }

        $this->myresponses->json($status, $message, $result);
    }

    public function get()
    {
        $status = 200;
        $message = "";
        $result = [];


            $result = $this->db->get("posts")->result();
            if ($result) {
                $message = "Data dial number";
            }
        

        $this->myresponses->json($status, $message, $result);
    }
}
