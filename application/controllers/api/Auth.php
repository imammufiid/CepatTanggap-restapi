<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    var $response;
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        $api_key = $this->input->get("api_key");
        if (empty($api_key)) {
            echo "Hello World";
        } else {
            echo "yes";
        }
    }

    public function login()
    {
        $username = $this->input->post("username");
        $password = $this->input->post("password");
        $status = 200;
        $message = "";
        $result = [];

        if (empty($username) || empty($password)) {
            $status = 400;
            $message = "Username atau Passoword harus diisi!";
        } else {
            $get_user = $this->db->get_where("users", ["username" => $username])->row();
            if ($get_user) {
                // checking users active
                if ($get_user->status != 1) {
                    $status = 400;
                    $message = "Maaf akun anda tidak aktif";
                }
                // checking password
                else if (password_verify($password, $get_user->password)) {
                    $message = "Berhasil Login...";
                    $result = $get_user;

                    // insert to log
                    $this->myresponses->log($get_user->id, $get_user->api_token, "Melakukan login");
                } else {
                    $status = 400;
                    $message = "Password anda salah!";
                }
            } else {
                $status = 400;
                $message = "Username anda salah!";
            }
        }

        $this->myresponses->json($status, $message, $result);
    }

    public function register()
    {
        $nama_user = $this->input->post("nama_user");
        $username = $this->input->post("email");
        $password = $this->input->post("password");
        $alamat = $this->input->post("alamat");
        $status = 201;
        $message = [];

        if (empty($username) || empty($password) || empty($nama_user)) {
            $status = 400;
            $message = "Form tidak boleh kosong!";
        } else {
            $data = [
                "username" => $username,
                "nama_user" => $nama_user,
                "alamat" => $alamat,
                "api_token" => random_string('md5', 50),
                "status" => 1,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "created_at" => date("Y-m-d H:i:s")
            ];
            $message = "Anda berhasil daftar!";
            $this->db->insert("users", $data);
            $insert_id = $this->db->insert_id();
            $result = $this->db->get_where('users', ["id", $insert_id])->row();
        }

        $this->myresponses->json($status, $message, $result);
    }

    public function editProfile()
    {
        $status = 201;
        $message = "Berhasil";
        $result = [];
        //var_dump($_FILES);die();
        // cek input
        $token = $this->input->post("token");
        $nama_lengkap = $this->input->post("nama_lengkap");
        $email = $this->input->post("email");
        $alamat = $this->input->post("alamat");
        $profile_path = $this->_unggahGambar();
        if ($profile_path['status'] == 400) {
            $status = $profile_path['status'];
            $message = $profile_path['message'];
        } else {
            // image
            // $name = $_FILES["image"]["name"];
            // //$ext = end((explode(".", $name))); # extra () to prevent notice

            // $config['upload_path']   = './assets/img/';
            // $config['allowed_types'] = 'gif|jpg|png';
            // $config['max_size']      = 0;

            // $this->load->library('upload', $config);

            // if (!$this->upload->do_upload()) {
            //     $status = 400;
            //     $message = "GAGAL";
            //     $result = 0;
            // } else {
            //     $upload_data = $this->upload->data();
            //     $profile_path = $upload_data['full_path'];
            // }

            $data = [
                'nama_user' => $nama_lengkap,
                'username' => $email,
                'alamat' => $alamat,
                'picture' => $profile_path['path']
            ];

            // update
            $this->db->where('api_token', $token);
            $this->db->update("users", $data);
            $result = $this->db->affected_rows();
        }



        $this->myresponses->json($status, $message, $result);
    }

    public function uploudImage()
    {
        $status = 201;
        $message = "Berhasil";
        $result = [];
        $file_name = "";

        $token = $this->input->post("token");
        $upload_image = $_FILES['image']['name'];
        $path = "";

        if ($upload_image) {
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = '100240';
            $config['upload_path']   = './assets/img/produk/';
            //$path = base_url() + './assets/img/produk/';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('image')) {
                $file_name = $this->upload->data('file_name');
                $path = $this->upload->data('full_path');

                $data = [
                    'picture' => $path
                ];

                // update
                $this->db->where('api_token', $token);
                $this->db->update("users", $data);
                $result = $this->db->affected_rows();
            } else {
                $status = 400;
                $message = "Gagal upload";
            }
        } else {
            $data['status'] = 400;
            $data['message'] = "Anda belum memasukkan gambar";
        }

        $this->myresponses->json($status, $message, $result);
    }

    private function do_upload()
    {
        $name = $_FILES["file"]["name"];
        $ext = end((explode(".", $name))); # extra () to prevent notice

        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size']      = 0;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        } else {
            $upload_data = $this->upload->data();

            #you can choose from
            /*
               Array(
                     [file_name]    => mypic.jpg
                     [file_type]    => image/jpeg
                     [file_path]    => /path/to/your/upload/
                     [full_path]    => /path/to/your/upload/jpg.jpg
                     [raw_name]     => mypic
                     [orig_name]    => mypic.jpg
                     [client_name]  => mypic.jpg
                     [file_ext]     => .jpg
                     [file_size]    => 22.2
                     [is_image]     => 1
                     [image_width]  => 800
                     [image_height] => 600
                     [image_type]   => jpeg
                     [image_size_str] => width="800" height="200"
              )
            */

            $this->model->insert_data($upload_data['file_name'], $upload_data['full_path']);

            $data = array('upload_data' => $this->upload->data());

            $this->load->view('upload_success', $data);
        }
    }

    public function _unggahGambar()
    {
        $status = 201;
        $message = "Berhasil";
        $result = [];
        $file_name = "";
        //echo json_encode($_FILES);die();
        $token = $this->input->post("token");
        $upload_image = $_FILES['image']['name'];
        $path = "";

        if ($upload_image) {
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']      = '100240';
            $config['upload_path']   = './assets/img/';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('image')) {
                $data['status'] = 201;
                $data['message'] = "Berhasil upload";
                $data['path'] = $this->upload->data('full_path');
                return $data;
            } else {
                $data['status'] = 400;
                $data['message'] = "Gagal Upload";
                $data['path'] = "";
                return $data;
            }
        } else {
            $data['status'] = 400;
            $data['message'] = "Gambar kosong";
            $data['path'] = "";
            return $data;
        }

        // $this->myresponses->json($status, $message, $result);

    }
}
