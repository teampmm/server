
<?php

    class Option{

        function dataNullCheck($json,$check_array){

            $invalid_data = array();

            foreach ($check_array as $check_data){

                if (empty($json[$check_data])){
                    array_push($invalid_data, $check_data);
                }
            }

            if ($invalid_data != null){
                $response_message = "";

                foreach ($invalid_data as $data)
                    $response_message = $response_message.$data.',';

                // 응답 데이터 마지막 문자 ',' 제거
                return "invalid_data : " . substr($response_message,0,-1);
            }

            else
                return null;
        }

        function actionLogData($log_array_data){

            $user_idx = 0;
            $politician_idx = 0;
            $bill_idx = 0;
            $party_idx = 0;
            $user_action = 0;


            foreach($log_array_data as $key=>$value){

                if($key == "user_idx")
                    $user_idx = (int)$value;
                elseif ($key == "politician_idx")
                    $politician_idx = (int)$value;
                elseif ($key == "bill_idx")
                    $bill_idx = (int)$value;
                elseif ($key == "party_idx")
                    $party_idx = (int)$value;
                elseif ($key == "user_action")
                    $user_action = $value;
            }

            $ip = $this->getUserIP(); # ip
            $bot_name = $this->botNameCheck(); # 크롤러 확인
            $browser = $this->getBrowserInfo(); #browser
            $device_os = $this->getOsInfo(); # os
            $referer = $this->getRefererInfo(); # os
            $yo_day = $this->dayCheck(); # 요일
            $visit_page = $this->getVisitPage(); # 방문페이지 확인
            $user_agent = $this->getUserAgent();

            $log_data = array(
                'user_idx' => $user_idx,
                'politician_idx' => $politician_idx,
                'bill_idx' => $bill_idx,
                'party_idx' => $party_idx,
                'user_action' => $user_action,

                'ip' => $ip,
                'user_agent' => $user_agent,
                'bot_name' => $bot_name,
                'visit_page' => $visit_page,
                'referer' => $referer,
                'device_os' => $device_os,

                'browser' => $browser,
                'yo_day' => $yo_day
            );
            if(strpos($visit_page, "favicon.ico") !== false){
                return "favicon.ico";
            }

            return $log_data;
        }

        // 유저 에이전트 정보 반환
        function getUserAgent(){
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if (!empty($user_agent)){
                return $user_agent;
            }
            else{
                return "user_agent is null";
            }
        }

        # 이전 페이지 정보
        function getRefererInfo(){
            # 이전페이지 정보가 없다면
            if (empty($_SERVER['HTTP_REFERER']) == true){
                return "no referer data";
            }
            # 이전페이지 정보가 있다면
            else{
                return $_SERVER['HTTP_REFERER'];
            }
        }

        // 방문한 페이지 확인
        function getVisitPage(){
            return $_SERVER['PHP_SELF'];
        }

        # 브라우저 확인
        function getBrowserInfo()
        {
            $user_agent = $this->getUserAgent();

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
        function getOsInfo()
        {
            $user_agent = $this->getUserAgent();

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
        function getUserIP(){
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
        function botNameCheck(){

            $user_agent = $this->getUserAgent();
            $ip = $this->getUserIP();

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

            if ($ip == "112.221.220.204"){
                return "정치왕_2사";
            }

            // 봇이 아님
            return 'other';
        }


    }





