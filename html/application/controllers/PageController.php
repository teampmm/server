<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PageController extends CI_Controller {

    // index 메인 페이지
    public function index()
    {
        $this->load->view('html/header.html');
        $this->load->view('html/index.html');
        $this->load->view('html/footer.html');
    }

    public function pageRequest($page){

        // 로그인 페이지
        if ($page == "login"){
            $this->load->view('html/header.html');
            $this->load->view('html/login.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 회원가입 약관 동의 페이지
        if ($page == "register_confirm_policy"){
            $this->load->view('html/header.html');
            $this->load->view('html/register_confirm_policy.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 회원가입 사용자 정보 입력 페이지
        if ($page == "register_input_info"){
            $this->load->view('html/header.html');
            $this->load->view('html/register_input_info.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 회원가입 완료 페이지
        if ($page == "register_finish"){
            $this->load->view('html/header.html');
            $this->load->view('html/register_finish.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 정치인 보기
        if ($page == "politician_search"){
            $this->load->view('html/header.html');
            $this->load->view('html/politician_search.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 정치인 자세히 보기
        if ($page == "politician_detail"){
            $this->load->view('html/header.html');
            $this->load->view('html/politician_detail.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        if ($page == "politician_detail_old"){
            $this->load->view('politician_detail_old');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 법안 보기
        if ($page == "bill_search"){
            $this->load->view('html/header.html');
            $this->load->view('html/bill_search.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 법안 상세 보기
        if ($page == "bill_detail"){
            $this->load->view('html/header.html');
            $this->load->view('html/bill_detail.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 정당 보기
        if ($page == "party_search"){
            $this->load->view('html/header.html');
            $this->load->view('html/party_search.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 정당 자세히 보기
        if ($page == "party_detail"){
            $this->load->view('html/header.html');
            $this->load->view('html/party_detail.html');
            $this->load->view('html/footer.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 내정보 보기
        if ($page == "my_info"){
            $this->load->view('my_info');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 개인정보취급방침 보기
        if ($page == "personal_information"){
            $this->load->view('personal_information');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 이용약관 보기
        if ($page == "use_agreement"){
            $this->load->view('use_agreement');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // 명예의전당 보기
        if ($page == "awards"){
            $this->load->view('html/awards.html');
            //방문 로그 생성
            $this->visitLogCreate();
        }

        // // 관리자 페이지
        // if ($page == "admin"){
        // 	$this->load->view("admin/phpSitemapNG/index");
        // }
        // 테스트용
        if ($page == "test"){
            $this->load->view('html/header.html');
            $this->load->view('html/link_facebook.html');
            $this->load->view('html/footer.html');
        }
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

