<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include 'DTO/Option.php';
include 'DTO/PolicticsJwt.php';

class DefaultController extends CI_Controller {

    public $option;
    public $token_str;

	public function index()
	{
//            // 지금 테스트를 위해 잠시 주석 처리를 해 놓았다.
//		// $this->load->view('html/header.html');
        $this->load->view('html/index.html');
		// $this->load->view('html/footer.html');

        $this->option = new Option();
        $this->actionLogStack("방문");

    }

    public function actionLogStack($action)
    {
        $pmm_jwt = new PolicticsJwt();

        // 회원
        if(!empty($_COOKIE['jwt'])){
            $this->token_str = $_COOKIE['jwt'];
            $token_data = $pmm_jwt->tokenParsing($this->token_str);

            $log_data = array(
                "user_idx" => $token_data->idx,
                "user_action" => $action
            );
            $visit_log_data = $this->option->actionLogData($log_data);
            if ($visit_log_data == "favicon.ico") return;

            $this->load->model('OptionModel');
            $this->OptionModel->actionLogInsert($visit_log_data);
        }
        // 비회원
        else{
            $log_data = array(
                "user_idx" => 0,
                "user_action" => $action
            );
            $visit_log_data = $this->option->actionLogData($log_data);
            if ($visit_log_data == "favicon.ico") return;

            $this->load->model('OptionModel');
            $this->OptionModel->actionLogInsert($visit_log_data);
        }
    }

}
