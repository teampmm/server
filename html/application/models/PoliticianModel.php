<?php

class PoliticianModel extends CI_Model
{

    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }

    // 정치인 카드 모아보기 정보
    public function getPoliticianCard($request_page, $random_card_idx, $token_data, $card_num, $generation){

        $elect_generation = $generation;

        /** 현재는 20대 국회의원 데이터 밖에없다.
         * 19대, 21대 등등 데이터를 db에 저장하게 되면 그떄 풀어줌
         */
        if($elect_generation != 20){
            header("HTTP/1.1 404 ");
            return '현재는 20대 국회의원 데이터 밖에 없습니다';
        }

        // 클라이언트가 첫 페이지 요청할때, 덱 번호가 정해져 있지 않아서 -1값으로 요청이 들어온다.
        $RANDOM_CARD_INIT_DATA = -1;

        // 응답 데이터
        $response_data = array();

        // 한 페이지에 보여줄 카드의 갯수
        $per_page_data = $card_num;

        $politician_num = $this->db->query("select count(idx) as cnt from PoliticianGeneration where generation = $elect_generation")->row();

        // 요청한 카드의 개수가 정치인 정보의 수보다 많은 경우 데이터가 그 만큼 없다고 처리해줘야함
        if ($card_num > $politician_num->cnt){
            header("HTTP/1.1 400 ");
            return '요청 카드의 개수를 줄여주세요. [최대 : '.$politician_num->cnt.']';
        }

        // 사용자가 가지고 있는 랜덤 카드 인덱스 - RandomCard 인덱스
        $current_rand_idx = $random_card_idx;

        // 첫페이지 요청
        if($current_rand_idx == $RANDOM_CARD_INIT_DATA){
            // 카드 인덱스, 카드 정보 가져오기 ( 정치인 인덱스가 들어있음 )
            $random_card_select_result = $this->db->query("select idx, card_number from RandomCard where generation = $elect_generation ORDER BY rand() limit 1 ")->row();
            $current_rand_idx = $random_card_select_result->idx;
        }

        // 첫페이지 이상 요청
        else{
            $random_card_select_result = $this->db->query("select idx, card_number from RandomCard where idx = '$current_rand_idx' and generation = $elect_generation")->row();
        }

        // 카드 정보 ( 정치인 인덱스가 들어있음 )
        $card_number = $random_card_select_result->card_number;

        // 문자열 제일 앞, 뒤에 있는 큰따옴표 제거
        $card_number = str_replace( "\"","", $card_number); // 쌍따옴표 제거

        // 카드 데이터 -> 카드 배열로 변환
        $card_number = explode(",", $card_number);

        // 카드 모음 리스트
        $card_list = array();

        // 북마크한 정치인의 인덱스를 담을 배열
        $book_mark_array = array();

        // 사용자가 로그인 했을때만 북마크 정보 저장
        if($token_data != null){
            // jwt 토큰에서 받은 아이디
            $user_idx = $token_data->idx;

            //사용자의 인덱스로 찾은 정치인 북마크 인덱스
            $book_mark_select_result = $this->db->query("select politician_idx from BookMark where user_idx = '$user_idx'")->result();

            // 배열에 정치인 북마크 인덱스 담기
            for ($i = 0 ;$i < count($book_mark_select_result); $i++ ){
                $book_mark_array[$i] = $book_mark_select_result[$i]->politician_idx;
            }
        }

        // 정치인 대수 테이블에서 generation == 20일떄 정치인 인덱스를 가져온다.
        // 정치인 인덱스를 가지고 Politician 테이블에서 카드 정보를 가져온다.
        $politician_generation_s_result=$this->db->query("select politician_idx from PoliticianGeneration where generation = $elect_generation")->result();

        // 20대 정치인 인덱스를 배열에 저장한다.
        $politician_idx_array = array();
        foreach ($politician_generation_s_result as $value) {
            array_push($politician_idx_array,(int)$value->politician_idx);
        }

        // 20대 정치인에 대한 정보를 검색
        $politician_info_result=$this->db->query("select * from Politician where `idx` IN (".implode(',',$politician_idx_array).")")->result();

        for ($i = $per_page_data * ($request_page-1); $i < $per_page_data * ($request_page-1) + $per_page_data; $i++){

            // 정치인 카드 갯수가 모자란다면 반복을 종료
            if($i >= count($card_number)){
                break;
            }

            // 카드 하나에 들어있는 데이터
            $card_data = array();

            // 카드번호에 해당하는 정치인 정보를 가져왔다.
            // 정치인 사진 경로
            // 정치인 이름
            // 정치인 정당 인덱스
            // 당선 지역
            // 카테고리
            $politician_idx = $politician_info_result[$card_number[$i]-1]->idx;
            $politician_party_s_result = $this->db->query("SELECT party_idx FROM PoliticianPartyHistory where politician_idx = $politician_idx and end_day is NULL ")->row();
            $politician_image_url = 'http://52.78.106.225/files/images/politician_thumbnail/'.$politician_info_result[$card_number[$i]-1]->idx.'.jpg';
            $politician_kr_name = $politician_info_result[$card_number[$i]-1]->kr_name;
            $politician_party_idx = $politician_party_s_result->party_idx;
            $politician_committee = $politician_info_result[$card_number[$i]-1]->committee;

            // 정당인덱스가 없다면 - DB에 값이 null인 경우임
            if($politician_party_idx == null){
                $party_name = null;
            }
            // 정당인덱스가 있다면
            else{
                $party_s_result = $this->db->query("SELECT * FROM Party where idx = $politician_party_idx")->row();
                $party_name = $party_s_result->name;
            }

            // 사용자가 로그인했을때만 북마크 여부를 보여준다.
            if($token_data->idx != "토큰실패"){
                if (in_array($politician_idx, $book_mark_array)){
                    $card_data['bookmark'] = true;
                }
                else{
                    $card_data['bookmark'] = false;
                }
            }

            $card_data['politician_idx'] = (int)$politician_idx;
            $card_data['politician_image_url'] = $politician_image_url;
            $card_data['politician_kr_name'] = $politician_kr_name;
            $card_data['party_name'] = $party_name;
            $card_data['politician_committee'] = $politician_committee;

            // 정치인 카드 리스트에 추가
            array_push($card_list,$card_data);
        }

        // 사용할 카드 덱의 번호
        $response_data['rand_card_idx'] = (int)$current_rand_idx;
        // 카드의 갯수
        $response_data['card_num'] = (int)count($card_list);
        // 현재 보고있는 페이지
        $response_data['current_page'] = (int)$request_page;
        // 총 페이지
        $response_data['total_page'] = (int)floor(count($card_number)/$per_page_data);
        // 카드 정보
        $response_data['card_list'] = $card_list;

        return json_encode($response_data);
    }

    // 배열 재 정렬
    public function arraySort($array)
    {
        //배열 인덱스 재정렬
        $i = 0;
        foreach ($array as $key => $val) {
            unset($array[$key]);

            $new_key = $i;
            $array[$new_key] = $val;
            $i++;
        }

        return $array;
    }

    // 정치인 기본정보 요청
    // input : 정치인 인덱스
    // output : 정치인 사진경로, 공약 이행률, 카테고리, 이름, 정당이름, 약력
    public function getInfo($politician_idx)
    {

        // 클라이언트에게 응답 해줄 데이터
        $response_data = array();

        // 정치인 기본 정보 조회 - 인덱스, 정당인덱스, 약력, 카테고리, 정치인 사진 경로
        $politician_select_result = $this->db->query("SELECT
                * FROM Politician where idx = '$politician_idx'")->row();

        // 정치인 조회 결과가 없음
        if($politician_select_result == null){
            $response_data['result'] = "정치인 정보가 없습니다.";
            header("HTTP/1.1 404 ");
            return json_encode($response_data);
        }

        // 정치인 조회 결과가 있음
        else{
            // Politician 테이블에서 조회한 데이터
            // 한글, 한자, 영어 이름 및 약력, 생년월일, 프로필 이미지 경로
            $response_data['politician_idx'] = $politician_select_result->idx;
            $response_data['kr_name'] = $politician_select_result->kr_name;
            $response_data['en_name'] = $politician_select_result->en_name;
            $response_data['ch_name'] = $politician_select_result->ch_name;
            $response_data['sex'] = $politician_select_result->sex;
            $response_data['committee'] = $politician_select_result->committee;
            $response_data['office_number'] = $politician_select_result->office_number;
            $response_data['email'] = $politician_select_result->email;
            $response_data['birthday'] = $politician_select_result->birthday;
            $response_data['history'] = $politician_select_result->history;
            $response_data['education'] = $politician_select_result->education;
            $response_data['soldier'] = $politician_select_result->soldier;
            $response_data['birth_area'] = $politician_select_result->birth_area;
            $response_data['twitter'] = $politician_select_result->twitter;
            $response_data['instagram'] = $politician_select_result->instagram;
            $response_data['blog'] = $politician_select_result->blog;
            $response_data['facebook'] = $politician_select_result->facebook;
            $response_data['youtube'] = $politician_select_result->youtube;
            $response_data['profile_image_url'] = 'http://52.78.106.225/files/images/politician_thumbnail/'.$politician_select_result->idx.'.jpg';

            return json_encode($response_data);
        }
    }

    // 정치인 관련 뉴스정보 요청
    // input : 정치인 인덱스
    // output : 뉴스제목, 뉴스날짜, 뉴스 링크 경로
    public function getNews($politician_idx)
    {
        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 정치인 관련 뉴스 조회
        $politician_select_result = $this->db->query("SELECT
                title, `date`, url
                FROM News where politician_idx = '$politician_idx'")->result();

        $result_num = count($politician_select_result);
        $response_data['result_num'] = (int)$result_num;
        $response_data['data'] = $politician_select_result;

        return json_encode($response_data);

    }

    // 정치인 공약 정보 요청
    // input : 정치인 인덱스
    // output : 공약내용, 공약대수, 공약 이행 상태
    public function getPledgeInfo($politician_idx){

        return "정치인 공약 데이터 없음 - 데이터 수집 못했음 ( 데이터 없어서 수작업 해야 할 수도 있음 )";

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 정치인 인덱스로 정치인 공약 모음 - 당선대수, 당선지역
        $politician_select_result = $this->db->query("SELECT
                * FROM Politician where idx = '$politician_idx'")->row();

        // 당선 대수
        $elect_generation = explode(',',$politician_select_result->elect_generation);

        // 당선 지역
        $elect_area = explode(',',$politician_select_result->elect_area);

        // 공약 정보를 담을 배열
        $pledge_data_array = array();

        for ($i = 0 ; $i < count($elect_generation); $i++){

            $pledge_data = array();

            // 정치인 공약 모음에서 대수, 공약이행상태, 내용을 가져온다.
            $politician_pledge_select_result = $this->db->query("SELECT
                pledge_implement_status, content
                FROM PoliticianPledge where 
                politician_idx = '$politician_idx' and 
                generation = '$elect_generation[$i]'")->result();

            $pledge_data['elect_generation'] = $elect_generation[$i];
            $pledge_data['elect_area'] = $elect_area[$i];
            $pledge_data['pledge_data'] = $politician_pledge_select_result;
            $response_data['kr_name'] = $politician_select_result->kr_name;
            array_push($pledge_data_array,$pledge_data);
        }
        $response_data['pledge_data'] = $pledge_data_array;

        return json_encode($response_data);
    }

    // 정치인 북마크 클릭/해제
    public function postBookmarkModify($politician_idx, $token_data){

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 유저 인덱스
        $user_idx = $token_data->idx;
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}


        // 사용자가 해당 정치인을 북마크 했는지 여부 보기
        $bookmark_select_result = $this->db->query("SELECT
                count(idx) as `count`  FROM BookMark where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

        // 해당 정치인을 북마크를 하고있었는데, 북마크 삭제 요청이 들어옴
        if ($bookmark_select_result->count == 1){
            $this->db->query("DELETE FROM BookMark WHERE user_idx = '$user_idx' and politician_idx = '$politician_idx'" );
            $response_data['result'] = '북마크 삭제';
            return json_encode($response_data);
        }

        // 해당 정치인을 북마크를 안하고 있었는데, 북마크 추가 요청이 들어옴
        else{
            $this->db->query("INSERT INTO BookMark VALUES 
                (null, $user_idx, null, $politician_idx, NOW(),NOW(),NOW())" );
            $response_data['result'] = '북마크 추가';
            return json_encode($response_data);
        }
    }

    public function getBookmark($politician_idx, $token_data){
        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

        // 사용자 인덱스
        $user_idx = $token_data->idx;

        // 좋아요 싫어요 정보조회
        $result = $this->db->query("SELECT * , count(*) as `count` FROM BookMark where 
                user_idx = $user_idx and politician_idx = $politician_idx")->row();

        if($result->count == 0){
            $response_data['status'] = '조회된 데이터가 없습니다';
        }
        else{
            $response_data['status'] = '현재 북마크중';
        }
        return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보수정
    public function postUserEvaluation($politician_idx, $like_status, $token_data){
        // 클라에게 보내줄 응답 데이터
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

        $response_data = array();

        // 클라이언트가 좋아요를 요청한경우
        if($like_status == "like"){
            $status = 1;
        }

        // 클라이언트가 싫어요를 요청한경우
        else{
            $status = 0;
        }

        // 사용자 인덱스
        $user_idx = $token_data->idx;

        // 사용자의 정치인에 대한 좋아요 데이터가 있는지 확인
        $bookmark_select_result = $this->db->query("SELECT
                count(idx) as `count`  FROM UserEvaluationBill where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

        // 좋아요 싫어요 데이터가 있는 경우
        if ($bookmark_select_result->count == 1){

            // 사용자의 정치인 좋아요에 대한 데이터가 있는가?
            $result = $this->db->query("select *, count(*) as `count` from UserEvaluationBill where 
                user_idx = '$user_idx' and politician_idx = '$politician_idx'")->row();

            // 데이터가 같은 경우 - 데이터 삭제
            if($result->count == 1){

                $current_status = (int)$result->status;

                if($current_status == $status){
                    // 좋아요 해제
                    $this->db->query("delete from UserEvaluationBill where user_idx= $user_idx and politician_idx= $politician_idx");
                    if($current_status == 1){
                        $response_data['result'] = "좋아요 해제";
                    }
                    // 싫어요 해제
                    else{
                        $response_data['result'] = "싫어요 해제";
                    }
                }
                else{
                    // 클라이언트가 좋아요 요청
                    if ($status==1){
                        $result=$this->db->query("update UserEvaluationBill set status=1 where user_idx='$user_idx' and politician_idx='$politician_idx'");
                        $response_data['result'] = "좋아요 활성화 싫어요 해제";
                    }else if ($status==0){
                        $result=$this->db->query("update UserEvaluationBill set status=0 where user_idx='$user_idx' and politician_idx='$politician_idx'");
                        $response_data['result'] = "좋아요 해제 싫어요 활성화";
                    }
                }
            }
        }

        // 좋아요 싫어요 데이터가 없는 경우
        else{
            $result = $this->db->query("INSERT INTO UserEvaluationBill VALUES 
                (null, null ,$politician_idx,$user_idx,$status)" );
            if($status == 1){
                $response_data['result'] = "좋아요 활성화";
            }
            else{
                $response_data['result'] = "싫어요 활성화";
            }
        }
        return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보 조회
    public function getUserEvaluation($politician_idx, $token_data){
        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 사용자 인덱스
        $user_idx = $token_data->idx;
        if($token_data->idx=="토큰실패"){$result_json=array();$result_json['result']='로그인 필요';header("HTTP/1.1 401 ");return json_encode($result_json);}

        // 좋아요 싫어요 정보조회
        $result = $this->db->query("SELECT * , count(*) as `count` FROM UserEvaluationBill where 
                user_idx = $user_idx and politician_idx = $politician_idx")->row();

        if($result->count == 0){
            $response_data['status'] = '조회된 데이터가 없습니다';
        }
        else{
            $response_data['status'] = $result->status;
        }
        return json_encode($response_data);
    }

    // 정치인 랜덤 카드 덱 만들기
    public function postMakeRandomCard(){

        // 대수
        $generation = 20;

        $result = $this->db->query("SELECT politician_idx FROM PoliticianGeneration where generation = $generation")->result();

        $temp = array();

        for ($i = 0; $i < 1000; $i++) {

            $random_idx_array = array();

            foreach ($result as $row) {
                array_push($random_idx_array, (int)$row->politician_idx);
                shuffle($random_idx_array);
            }
            $temp[$i] = $random_idx_array;

            $card_number_str = "";

            for($j = 0; $j<count($temp[$i]); $j++){
                $card_number_str = $card_number_str.$temp[$i][$j].",";
            }


            $this->db->query("INSERT INTO RandomCard VALUES (null, $generation,'$card_number_str')" );
        }
    }

}


// 사용하지 않는 메서드

// 정치인 상세 정보 요청
// input : 정치인 이름
// output : 당선대수, 당선횟수, 당선지역, 소속위원회, 약력, 연락처
//public function getDetailInfo($data){
//
//	// 정치인 이름
//	$politician_idx = $data['idx'];
//
//	// 정치인 조회 결과가 없음
//	if($politician_idx == null) return 'invalid_data_[idx]';
//
//	// 정치인 이름으로 상세정보 찾기
//	$politician_select_result = $this->db->query("SELECT
//                kr_name, ch_name, en_name, office_number, history, profile_image_url, affiliation_committee,
//                email_id, email_address, aide, secretary, elect_generation, elect_area
//                FROM Politician where idx = '$politician_idx'")->row();
//
//	// 클라에게 보내줄 응답 데이터
//	$response_data = array();
//
//	$response_data['result'] = $politician_select_result;
//
//	return json_encode($response_data);
//
//}
