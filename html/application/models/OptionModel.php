<?php

class OptionModel extends CI_Model{

    function blackListTokenCheck($token_str){
        $query = $this->db->query("select count(idx) as 'count' from BlackList where 
                    token='$token_str'"
        )->row();

        if ($query->count >= 1){
            header("HTTP/1.1 401 ");
            return '토큰 만료 - 재 로그인 바람';
        }
        else{
            return "유효한 토큰";
        }
    }

    function logRecode($log_data_array){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $message = "ip : ". $ip. ', ';

        foreach ($log_data_array as $key=>$value){
            $message = $message.$key.' : '.$value.', ';
        }
        $message = substr($message,0,-2);

        log_message("error", $message);
    }

    // 사용자가 홈페이지를 방문했을때 로그를 쌓는다.
    // ip, id, visit_page, referer, user_agent, create_at
    function visitLogCreate($log_data){

        $sql = "insert into Log (
                    ip, 
                    id, 
                    bot_name,
                    visit_page, 
                    referer, 
                    user_agent, 
                    device_os, 
                    browser,
                    yo_day,
                    create_at) 
                value (?,?,?,?,?,?,?,?,?,NOW())";
        $this->db->query($sql,array(
            $log_data['ip'],
            $log_data['id'],
            $log_data['bot_name'],
            $log_data['visit_page'],
            $log_data['referer'],
            $log_data['user_agent'],
            $log_data['device_os'],
            $log_data['browser'],
            $log_data['yo_day'],
        ));

    }


}