<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once 'OptionModel.php';

class UserModel extends CI_Model
{
    public $option_model;

    public function __construct()
    {
        parent::__construct();
        $this->option_model = new OptionModel();
    }

    // 사용자 회원가입
    public function putUser($data)
    {
        $id = $data['id'];
        $nick_name = $data['nick_name'];
        $phone = $data['phone'];

        // 클라이언트에서 hash 암호화 된 상태로 서버에게 전달해줌
        $pw = $data['pw'];

        $sql = "select count(idx) as idx from User where id = ? or nick_name = ? or phone= ?";

        $query = $this->db->query($sql, array($id, $nick_name, $phone))->row();

        if ($query->idx >= 1) {
            $response_data['result'] = "이미 가입된 계정입니다";
            return json_encode($response_data);
        } else {


            $sql = "insert into User (name,age,nick_name,sex,id,pw,phone,residence,social_login,create_at,user_agent) 
                value (?,?,?,?,?,?,?,?,?,?,?)";

            $name = $data['name'];
            $age = $data['age'];
            $sex = $data['sex'];
            $residence = $data['residence'];
            $social_login = $data['social_login'];
            $now_time = date("Y-m-d H:i:s");
            $user_agent = $data['user_agent'];

            $result = $this->db->query($sql, array($data['name'], $data['age'], $nick_name, $data['sex'],
                $id, $pw, $phone, $data['residence'],
                $data['social_login'], $now_time, $data['user_agent']));

            $response_data = array();

            // 회원 정보 추가 성공
            if ($result == 1) {
                header("HTTP/1.1 201");
                $response_data['result'] = "회원가입 성공";
                return json_encode($response_data, JSON_UNESCAPED_UNICODE);
            } // 회원 정보 추가 실패
            else {
                $response_data['result'] = "잠시후 다시 시도해주세요";
                return json_encode($response_data, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    // 사용자가 로그인 요청 - email정보와, 패스워드 정보를 입력으로 받는다.
    public function getLoginStatus($json_data, $user_info)
    {
        // client가 보낸 사용자 id
        $id = $json_data['id'];

        // 사용자 패스워드는 암호화 된 채로 들어온다.
        $pw = $json_data['pw'];

        $sql = "select idx, count(idx) as 'count' from User where 
                    id = ? and pw = ?";

        $query = $this->db->query($sql, array($id, $pw))->row();

        // 사용자 정보가 일치
        if ($query->count == 1) {

            // 최근 로그인 시간 업데이트
            $sql = "update User set recently_login_at = ? where idx = ?";
            $this->db->query($sql, array(date("Y-m-d H:i:s"), $query->idx));

            // jwt 토큰 객체 생성
            $pmm_jwt = new PolicticsJwt();

            // 사용자 id 값으로 토큰을 생성해서 client에게 전달해준다.
            // 이제부터 클라이언트는 api 요청 시 서버로 부터 받은 토큰을 사용 하여 필요한 데이터를 주고 받는다.
            $token = $pmm_jwt->createToken($user_info);

            $response_data['result'] = "로그인 성공";
            $response_data['token'] = $token;
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } // 사용자 정보가 불일치
        else {
            $response_data['result'] = "가입된 계정이 없거나, 비밀번호가 틀렸습니다";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }
    }

    // 닉네임 중복 체크
    // 이메일 중복이 안될 시 return success
    // 이메일 중복 시 return failed
    public function getNickCheck($nick_name)
    {
        $sql = "select count(idx) as 'count' from User where nick_name = ? ";

        $query = $this->db->query($sql, array($nick_name))->row();

        // 클라에게 보낼 응답 데이터
        $response_data = array();

        // 닉네임 중복
        if ($query->count == 1) {
            $response_data['result'] = "닉네임중복-가입불가능";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } // 닉네임 중복이 아님
        else {
            $response_data['result'] = "닉네임중복아님-가입가능";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }
    }

    // 아이디 중복체크
    public function getIdCheck($id)
    {
        $sql = "select count(idx) as 'count' from User where id = ? ";

        $query = $this->db->query($sql, array($id))->row();

        // 클라에게 보낼 응답 데이터
        $response_data = array();

        // 닉네임 중복
        if ($query->count == 1) {
            $response_data['result'] = "아이디중복-가입불가능";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } // 닉네임 중복이 아님
        else {
            $response_data['result'] = "아이디중복아님-가입가능";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }
    }

    //핸드폰 인증을 하기전에 우선 가입이 되어있는지 확인
    public function phoneCheck($phone)
    {
        $sql = "select count(idx) as 'count' from User where phone = ? ";

        $count = $this->db->query($sql, array($phone))->row();

        // 가입 가능
        if ($count->count == 0) {
            return 0;
        } // 가입 불가능
        else {
            return 1;
        }
    }
    //카카오 로그인일때 db와 uid를 비교한다
    //경우 1 . 처음 회원가입
    //경우 2 . 카카오 동의만 받고 회원가입을 진행 하지 않음
    //경우 3 . 카카오 로그인으로 회원가입 진행
    public function kakaoCheck($uid, $user_info)
    {

        $sql = "select *,count(idx)as `count` from User where id = ? and social_login='K'";

        $result = $this->db->query($sql, array($uid))->row();

        //이미 테이블에 카카오 uid가 저장되어있는경우
        $result_json = array();
        if ($result->count == 1) {
            //카카오 동의만 받고 가입을 진행하지 않은 상태
            //pmm자체 회원가입 페이지로 넘어가야함
            //pmm자체 회원가입으로 넘어가야함 = 1
            if ($result->nick_name == null) {
                $result_json['response_code'] = 1;
                return json_encode($result_json, JSON_UNESCAPED_UNICODE);
            }
            //카카오 동의후 pmm회원가입까지 완료한 상태
            //카카오 로그인 완료 = 0
            else {

                // 최근 로그인 시간 업데이트
                $sql = "update User set recently_login_at = ? where idx = ?";
                $this->db->query($sql, array(date("Y-m-d H:i:s"), $result->idx));

                // jwt 토큰 객체 생성
                $pmm_jwt = new PolicticsJwt();

                // 사용자 id 값으로 토큰을 생성해서 client에게 전달해준다.
                // 이제부터 클라이언트는 api 요청 시 서버로 부터 받은 토큰을 사용 하여 필요한 데이터를 주고 받는다.
                $token = $pmm_jwt->createToken($user_info);
                $result_json['response_code'] = 0;
                $result_json['token'] = $token;
                return json_encode($result_json, JSON_UNESCAPED_UNICODE);
            }
        }
        //첫가입
        //카카오 정보 저장 완료 회원가입으로 넘어가야함 = 2
        else {

            $sql = "INSERT INTO User (social_login,id) VALUES ('K', ?)";

            $this->db->query($sql, array($uid));

            $result_json['response_code'] = 2;
            return json_encode($result_json, JSON_UNESCAPED_UNICODE);
        }

    }

    //카카오 동의후 회원가입을 위한 메서드
    public function putKakaoUser($userinfo)
    {
        $name = $userinfo['name'];
        $age = (int)$userinfo['age'];
        $nick_name = $userinfo['nick_name'];
        $sex = $userinfo['sex'];
        $phone = $userinfo['phone'];
        $residence = $userinfo['residence'];
        $category = $userinfo['category'];
        $user_agent = $userinfo['user_agent'];
        $uid = $userinfo['kakao_uid'];

        $sql = "update User set `name` = ?, 
                                age = ?, 
                                nick_name = ?, 
                                sex = ?, 
                                phone = ?, 
                                residence = ?, 
                                category = ?, 
                                create_at = ?, 
                                user_agent = ? 
                where id = ?";

        // 현재시간
        $now_time = date("Y-m-d H:i:s");

        $result = $this->db->query($sql, array($name, $age, $nick_name, $sex, $phone, $residence, $category, $now_time, $user_agent, $uid));

        header("HTTP/1.1 201");
        $result_json['response_code'] = (boolean)$result;
        return json_encode($result_json, JSON_UNESCAPED_UNICODE);
    }

    // 사용자 정보를 반환하는 메서드 NO API
    public function getUserInfo($user_id)
    {

        $sql = "select * from User where id = ?";
        $user_info = $this->db->query($sql, array($user_id))->row();
        return $user_info;
    }

    // 로그아웃 요청 메서드
    public function logOutRequest($token_data, $token_str)
    {

        $response_data = array();

        if ($token_data->idx != "토큰실패") {

            $sql = "insert into BlackList VALUE (null , ? ,null ,null, ?,null ,null )";
            $now_time = date("Y-m-d H:i:s");
            $this->db->query($sql, array($token_str, $now_time));

            $response_data['result'] = "로그아웃 완료 토큰삭제바람";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } else {
            header("HTTP/1.1 401");
            $response_data['result'] = "토큰값이 없습니다";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }

    }

    // 패스워드 변경
    public function pwChange($token_data, $current_pw, $update_pw)
    {

        $current_pw = (string)$current_pw;
        $update_pw = (string)$update_pw;

        $sql = "select count(idx) as cnt from User where idx = ? and pw = ?";
        $sql_result = $this->db->query($sql, array((int)$token_data->idx, $current_pw))->row();

        if ($sql_result->cnt == 0) {
            $response_data['result'] = "현재 비밀번호가 일치하지 않습니다";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } else {
            if ($token_data->idx != "토큰실패") {

                $now_time = date("Y-m-d H:i:s");

                $sql = "update User set pw = ?, update_at = ? where idx = ?";
                $this->db->query($sql, array($update_pw, $now_time, (int)$token_data->idx));

                $response_data['result'] = "비밀번호 변경완료";
                return json_encode($response_data, JSON_UNESCAPED_UNICODE);
            } else {
                header("HTTP/1.1 401");
                $response_data['result'] = "토큰값이 없습니다";
                return json_encode($response_data, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    // 닉네임 변경
    public function nickChange($token_data, $nickname)
    {

        $nickname = (string)$nickname;

        if ($token_data->idx != "토큰실패") {

            $now_time = date("Y-m-d H:i:s");

            $sql = "update User set nick_name = ?, update_at = ? where idx = ?";
            $this->db->query($sql, array($nickname, $now_time, (int)$token_data->idx));

            $response_data['result'] = "닉네임 변경완료";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } else {
            header("HTTP/1.1 401");
            $response_data['result'] = "토큰값이 없습니다";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }

    }

    // 사용자 정보 조회
    public function getInfo($token_data)
    {
        if ($token_data->idx != "토큰실패") {

            $sql = "select * from User where idx = ? ";
            $sql_result = $this->db->query($sql, array($token_data->idx))->row();

            $user_info = array();

            $user_info['idx'] = (int)$sql_result->idx;
            $user_info['name'] = $sql_result->name;
            $user_info['age'] = (int)$sql_result->age;
            $user_info['nick_name'] = $sql_result->nick_name;
            $user_info['sex'] = $sql_result->sex;
            $user_info['id'] = $sql_result->id;
            $user_info['phone'] = $sql_result->phone;
            $user_info['residence'] = $sql_result->residence;
            $user_info['category'] = $sql_result->category;

            $response_data['user_info'] = $user_info;
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        } else {
            header("HTTP/1.1 401");
            $response_data['result'] = "토큰값이 없습니다";
            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }
    }

    // 임시 비밀번호 발급
    public function tempPw($id, $phone)
    {

        $response_data = array();

        $sql = "select idx from User where id = ? and phone = ?";
        $sql_result = $this->db->query($sql, array($id, $phone))->row();

        // 사용자 정보가 있는경우,
        // 임시 비밀번호를 해당 사용자 휴대폰 번호로 알려주고 비밀번호를 업데이트 시켜줘야함
        if ($sql_result != null) {
            $response_data['result'] = "essential_pw_update!";
        } else {
            $response_data['result'] = "일치하는 회원정보가 없습니다";
        }

        return $response_data;
    }

    // 임시 비밀번호로 변경하는 메서드
    public function tempPwChange($id, $phone, $temp_pw)
    {

        $temp_pw = (string)$temp_pw;

        $hash_temp_pw = hash("sha256", $temp_pw);

        $now_time = date("Y-m-d H:i:s");

        $sql = "update User set pw = ?, update_at = ? where id = ? and phone = ?";
        $this->db->query($sql, array($hash_temp_pw, $now_time, $id, $phone));

        $response_data['result'] = "임시 비밀번호 발급완료";
        return $response_data;

    }

    // 휴대폰으로 인증코드를 발송하는 메서드
    public function receiveCode($phone, $code)
    {
        $sql = "select count(idx) as cnt from User where phone = ? ";

        $result = $this->db->query($sql, array($phone))->row()->cnt;

        if ($result == 1){
            $now_time = date("Y-m-d H:i:s");

            $sql = "insert into PhoneCode (phone, code, create_at) 
                value (?,?,?)";

            $this->db->query($sql, array($phone, $code, $now_time));
            return "회원정보 있음";
        }
        else{
            return "일치하는 회원 정보가 없음";
        }


    }

    public function getPhoneCodeCheck($phone, $code)
    {
        $response_data = array();

        $sql = "select count(idx) as cnt from User where phone = ? ";

        $result = $this->db->query($sql, array($phone))->row()->cnt;

        if ($result == 0){
            $response_data['result'] = "일치하는 회원 정보가 없음";
            return $response_data;
        }


        $now_time = date("Y-m-d H:i:s");

        $sql = "select * from PhoneCode where phone = ? and code = ?";

        $result = $this->db->query($sql, array($phone, $code))->row();

        if ($result == null) {
            $response_data['result'] = "인증 코드가 맞지 않습니다";
            return $response_data;
        }

        $now_time = explode(' ', $now_time)[1];
        $now_time_hour = explode(':', $now_time)[0];
        $now_time_minute = explode(':', $now_time)[1];
        $now_time_sec = explode(':', $now_time)[2];

        $create_at_time = explode(' ', $result->create_at)[1];
        $create_at_hour = explode(':', $create_at_time)[0];
        $create_at_minute = explode(':', $create_at_time)[1];
        $create_at_sec = explode(':', $create_at_time)[2];


        if ($now_time_hour != $create_at_hour) {
            $response_data['result'] = "인증코드 시간이 유효하지 않습니다. 다시 인증해주세요";
            return $response_data;
        } else if ($now_time_minute > $create_at_minute + 5) {
            $response_data['result'] = "인증코드 시간이 유효하지 않습니다. 다시 인증해주세요";
            return $response_data;
        }

        if ($result != null) {

            $sql = "delete from PhoneCode where phone = ?";
            $this->db->query($sql, array($phone));

            $sql = "select id from User where phone = ?";
            $user_id = $this->db->query($sql, array($phone))->row()->id;

            $response_data['result'] = "인증되었습니다";
            $response_data['user_id'] = $user_id;
            return $response_data;
        }

    }
}


//-------------------------------------------------------------------------
// DB 테이블 수정 후 사용 안하는 메서드

//-------------------------------------------------------------------------
//    // 이메일 중복 체크
//    // 이메일 중복이 안될 시 return success
//    // 이메일 중복 시 return failed
//    public function getEmailCheck($data)
//{
//    // client가 보낸 사용자 이메일
//    $email_id = $data['email_id'];
//    $email_address = $data['email_address'];
//
//    // client로 부터 입력 받은 이메일 주소가 있는지 조회 - 중복체크를 위함.
//    $query = $this->db->query("select count(idx) as `count` from User where
//                email_id= '$email_id' and
//                email_address = '$email_address'"
//    )->row();
//
//    // 이메일 중복
//    if ($query->count == 1) {
//        return '중복';
//    }
//    // 이메일 중복이 아님
//    else {
//        return '중복아님';
//    }
//}
//-------------------------------------------------------------------------

//-------------------------------------------------------------------------
//// 사용자 이메일로 인증코드를 보내는 함수
//    public function sendEmailCode($data)
//{
//    // phpmailer 사용을 위한 파일 불러오기
//    require 'phpmailer/phpmailer/src/Exception.php';
//    require 'phpmailer/phpmailer/src/PHPMailer.php';
//    require 'phpmailer/phpmailer/src/SMTP.php';
//
//    // 관리자 이메일
//    $admin_email = "teampmm2020@gmail.com";
//
//    // 관리자 패스워드 - 구글 2차 인증 비밀번호
//    $admin_pw = "wfsoyyiezyapbnwi";
//
//    // 사용자 이메일
//    $user_email = $data['email_id'] . '@' . trim($data['email_address']);
//
//    // PHPMailer 객체 생성
//    $mail = new PHPMailer();
//
//    try {
//        // $mail->SMTPDebug = 2; // 디버깅 설정 - 주석 해제 시 출력문에 메일을 발송 하는 과정이 나온다.
//        $mail->isSMTP(); // SMTP 사용 설정
//
//        // 지메일일 경우 smtp.gmail.com,
//        // 네이버일 경우 smtp.naver.com
//        $mail->Host = "smtp.gmail.com";               // 구글 smtp 서버
//        $mail->SMTPAuth = true;                         // SMTP 인증을 사용함
//        $mail->Username = $admin_email;    // 메일 계정 (지메일일경우 지메일 계정)
//        $mail->Password = $admin_pw;                  // 메일 비밀번호
//        $mail->SMTPSecure = "ssl";                       // SSL을 사용함
//        $mail->Port = 465;                                  // email 보낼때 사용할 포트를 지정
//        $mail->CharSet = "utf-8"; // 문자셋 인코딩
//
//        // 보내는 메일
//        $mail->setFrom($admin_email, "보내는 사람 이름");
//
//        // 받는 메일
//        $mail->addAddress($user_email, "받는사람 이름");
//
//        // 첨부파일
////             $mail->addAttachment("a.jpg");
//        // $mail->addAttachment("./test2.jpg");
//
//        // 메일 내용
//        $mail->isHTML(true); // HTML 태그 사용 여부
//        $mail->Subject = "TeamPmm 회원가입 인증 메일입니다";  // 메일 제목
//        $mail->Body = "";     // 메일 내용
//
//        // Gmail로 메일을 발송하기 위해서는 CA인증이 필요하다.
//        // CA 인증을 받지 못한 경우에는 아래 설정하여 인증체크를 해지하여야 한다.
//        $mail->SMTPOptions = array(
//            "ssl" => array(
//                "verify_peer" => false
//                , "verify_peer_name" => false
//                , "allow_self_signed" => true
//            )
//        );
//        // 메일 전송
//        $mail->send();
//
//        // 메일 전송 성공
//        return "성공";
//
//    } catch (Exception $e) {
//
//        // 메일 전송 실패
//        return "실패";
//    }
//}
//-------------------------------------------------------------------------
