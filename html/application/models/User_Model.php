<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User_Model extends CI_Model
{
    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }

    // 사용자 데이터 db에 추가
    public function putUser($data)
    {
        // 앱 버전 관리 - 2020-04-27 기준준 전 1.0
        $app_version = "1.0";

        $input_data = array(
            'idx' => null,
            'bookmark_politician_idx' => null,
            'bookmark_bill_idx' => null,
            'name' => $data['name'],
            'age' => $data['age'],
            'nick_name' => $data['nick_name'],
            'sex' => $data['sex'],
            'email_id' => $data['email_id'],
            'email_address' => $data['email_address'],
            'pw' => hash('sha256', $data['pw']),
            'phone' => $data['phone'],
            'residence' => $data['residence'],
            'social_login' => $data['social_login'],
            'create_at' => date("Y-m-d H:i:s"),
            'update_at' => null,
            'delete_at' => null,
            'recently_login_at' => null,
            'device_id' => $data['device_id'],
            'device_model' => $data['device_model'],
            'device_os' => $data['device_os'],
            'app_version' => $app_version
        );

        // insert ( '테이블명', 배열 데이터 )
        $result = $this->db->insert('User', $input_data);

        // 회원 정보 추가 성공
        if ($result == 1) {
            return '성공';
        }
        // 회원 정보 추가 실패
        else {
            return '실패';
        }
    }


    // 사용자가 로그인 요청 - email정보와, 패스워드 정보를 입력으로 받는다.
    public function getLoginStatus($data)
    {
        // client가 보낸 사용자 이메일
        $email_id = $data['email_id'];
        $email_adderss = $data['email_address'];

        // 사용자 비밀번호 sha256으로 암호화
        $pw = hash('sha256', $data['pw']);
        //echo $email_id . $email_adderss . $pw;

        // client로 부터 입력받은 id, pw에 대한 사용자 정보가 일치 하는지 조회
        $query = $this->db->query("select count(idx) as 'count' from User where 
                    email_id='$email_id' and 
                    email_address='$email_adderss' and 
                    pw='$pw'"
        )->row();

        // 사용자 정보가 일치
        if ($query->count == 1) {
            return '성공';
        }

        // 사용자 정보가 불일치
        else {
            return '실패';
        }
    }

    // 이메일 중복 체크
    // 이메일 중복이 안될 시 return success
    // 이메일 중복 시 return failed
    public function getEmailCheck($data)
    {
        // client가 보낸 사용자 이메일
        $email_id = $data['email_id'];
        $email_address = $data['email_address'];

        // client로 부터 입력 받은 이메일 주소가 있는지 조회 - 중복체크를 위함.
        $query = $this->db->query("select count(idx) as `count` from User where 
                email_id= '$email_id' and 
                email_address = '$email_address'"
        )->row();

        // 이메일 중복
        if ($query->count == 1) {
            return '중복';
        }
        // 이메일 중복이 아님
        else {
            return '중복아님';
        }
    }

    // 닉네임 중복 체크
    // 이메일 중복이 안될 시 return success
    // 이메일 중복 시 return failed
    public function getNickCheck($data)
    {
        // client가 보낸 사용자 닉네임
        $nickname = $data['nick_name'];

        // client로 부터 입력 받은 닉네임이 있는지 조회 - 중복체크를 위함.
        $query = $this->db->query("select count(idx) as 'count' from User where 
                nick_name='$nickname'"
        )->row();

        // 닉네임 중복
        if ($query->count == 1) {
            return '중복';
        }
        // 닉네임 중복이 아님
        else {
            return '중복아님';
        }
    }

    // 사용자 이메일로 인증코드를 보내는 함수
    public function sendEmailCode($data)
    {
        // phpmailer 사용을 위한 파일 불러오기
        require 'phpmailer/phpmailer/src/Exception.php';
        require 'phpmailer/phpmailer/src/PHPMailer.php';
        require 'phpmailer/phpmailer/src/SMTP.php';

        // 관리자 이메일
        $admin_email = "teampmm2020@gmail.com";

        // 관리자 패스워드 - 구글 2차 인증 비밀번호
        $admin_pw = "wfsoyyiezyapbnwi";

        // 사용자 이메일
        $user_email = $data['email_id'] . '@' . trim($data['email_address']);

        // PHPMailer 객체 생성
        $mail = new PHPMailer();

        try {
            // $mail->SMTPDebug = 2; // 디버깅 설정 - 주석 해제 시 출력문에 메일을 발송 하는 과정이 나온다.
            $mail->isSMTP(); // SMTP 사용 설정

            // 지메일일 경우 smtp.gmail.com,
            // 네이버일 경우 smtp.naver.com
            $mail->Host = "smtp.gmail.com";               // 구글 smtp 서버
            $mail->SMTPAuth = true;                         // SMTP 인증을 사용함
            $mail->Username = $admin_email;    // 메일 계정 (지메일일경우 지메일 계정)
            $mail->Password = $admin_pw;                  // 메일 비밀번호
            $mail->SMTPSecure = "ssl";                       // SSL을 사용함
            $mail->Port = 465;                                  // email 보낼때 사용할 포트를 지정
            $mail->CharSet = "utf-8"; // 문자셋 인코딩

            // 보내는 메일
            $mail->setFrom($admin_email, "보내는 사람 이름");

            // 받는 메일
            $mail->addAddress($user_email, "받는사람 이름");

            // 첨부파일
            // $mail->addAttachment("./test1.zip");
            // $mail->addAttachment("./test2.jpg");

            // 메일 내용
            $mail->isHTML(true); // HTML 태그 사용 여부
            $mail->Subject = "TeamPmm 회원가입 인증 메일입니다";  // 메일 제목
            $mail->Body = "이메일 내용";     // 메일 내용

            // Gmail로 메일을 발송하기 위해서는 CA인증이 필요하다.
            // CA 인증을 받지 못한 경우에는 아래 설정하여 인증체크를 해지하여야 한다.
            $mail->SMTPOptions = array(
                "ssl" => array(
                    "verify_peer" => false
                , "verify_peer_name" => false
                , "allow_self_signed" => true
                )
            );
            // 메일 전송
            $mail->send();

            // 메일 전송 성공
            return "성공";

        } catch (Exception $e) {

            // 메일 전송 실패
            return "실패";
        }
    }
}

