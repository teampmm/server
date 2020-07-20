<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DefaultController extends CI_Controller {

	public function index()
	{
//            // 지금 테스트를 위해 잠시 주석 처리를 해 놓았다.
//		// $this->load->view('html/header.html');
        $this->load->view('html/index.html');
		// $this->load->view('html/footer.html');
        $this->visitLogCreate();
	}

    // 홈페이지 방문자 로그 쌓기
    public function visitLogCreate(){

        $user_agent = $_SERVER["HTTP_USER_AGENT"]; # user_agent
        $bot_name = $this->botNameCheck($user_agent);
        $browser = $this->getBrowserInfo($user_agent); #browser
        $device_os = $this->getOsInfo($user_agent); # os
        $referer = $this->getRefererInfo(); # os
        $yo_day = $this->dayCheck(); # 요일
        $visit_page = $this->visitPageCheck();
        $ip = $this->ipCheck();

        # id, visit_page, referer, user_agent, device_os, browser, yo_day
        $log_data = array(
            'ip' => $ip,
            'id' => null,
            'bot_name' => $bot_name,
            'visit_page' => $visit_page,
            'referer' => $referer,
            'user_agent' => $user_agent,
            'device_os' => $device_os,
            'browser' => $browser,
            'yo_day' => $yo_day
        );

        $this->load->model('OptionModel');
        $this->OptionModel->visitLogCreate($log_data);
    }

    # 이전 페이지 정보
    function getRefererInfo(){
        # 이전페이지 정보가 없다면
        if (empty($_SERVER['HTTP_REFERER']) == true){
            return "페이지 새로고침";
        }
        # 이전페이지 정보가 있다면
        else{
            return $_SERVER['HTTP_REFERER'];
        }
    }

    function visitPageCheck(){
        return $_SERVER['PHP_SELF'];
    }

    # 유저 에이전트로 브라우저 확인하기
    function getBrowserInfo($user_agent)
    {
        if(preg_match('/MSIE/i',$user_agent) && !preg_match('/Opera/i',$user_agent)){
            $browser = 'Internet Explorer';
        }
        else if(preg_match('/Firefox/i',$user_agent)){
            $browser = 'Mozilla Firefox';
        }
        else if (preg_match('/Chrome/i',$user_agent)){
            $browser = 'Google Chrome';
        }
        else if(preg_match('/Safari/i',$user_agent)){
            $browser = 'Apple Safari';
        }
        elseif(preg_match('/Opera/i',$user_agent)){
            $browser = 'Opera';
        }
        elseif(preg_match('/Netscape/i',$user_agent)){
            $browser = 'Netscape';
        }
        else{
            $browser = "Other";
        }

        return $browser;
    }

    # 유저 에이전트로 os 확인하기
    function getOsInfo($user_agent)
    {
        if (preg_match('/linux/i', $user_agent)){
            $os = 'linux';}
        elseif(preg_match('/macintosh|mac os x/i', $user_agent)){
            $os = 'mac';}
        elseif (preg_match('/windows|win32/i', $user_agent)){
            $os = 'windows';}
        else {
            $os = 'Other';}
        return $os;
    }

    # 사용자 ip 확인
    function ipCheck(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    # 사용자 접속 요일 확인
    function dayCheck(){
        $arrDay= array('일요일','월요일','화요일','수요일','목요일','금요일','토요일');
        $date = date('w'); //0 ~ 6 숫자 반환
        return $arrDay[$date];
    }

    // 크롤러 ( 봇 ) 인지 아닌지 체크 ( 방문 사용자만 확인하기 위함 )
    function botNameCheck($user_agent){
        // Yeti는 네이버라고함
        $search_bot_array=array('Googlebot','Yeti','bingbot','msnbot','Yahoo! Slurp');
        foreach($search_bot_array as $search_bot)
        {
            $SearchBotValid = strpos($user_agent, $search_bot);

            // 봇인경우
            if($SearchBotValid !== false)   {
                return $search_bot;
            }
        }

        // 봇이 아님
        return 'user';
    }

}
