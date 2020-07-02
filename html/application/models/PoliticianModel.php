<?php

include_once 'OptionModel.php';

class PoliticianModel extends CI_Model
{
    public $option_model;

    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
        $this->option_model = new OptionModel();
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

        $sql = "SELECT count(idx) as cnt FROM Politician";

        $result = $this->db->query($sql)->row();

        if ((int)$result->cnt < (int)$politician_idx or (int)$politician_idx < 0) {
            header("HTTP/1.1 400");
            return;
        } // 정치인 조회 결과가 있음
        else {
            $sql = "SELECT * FROM Politician where idx = ?";
            $politician_select_result = $this->db->query($sql, array($politician_idx))->row();

            // 사용자의 sql 및 id 등 로그 기록하기
            $log_sql = "SELECT * FROM Politician where idx = $politician_idx";
            $this->option_model->logRecode(
                array(
                    'sql' => $log_sql
                )
            );

            // Politician 테이블에서 조회한 데이터
            // 한글, 한자, 영어 이름 및 약력, 생년월일, 프로필 이미지 경로
            $response_data['politician_idx'] = (int)$politician_select_result->idx;
            $response_data['kr_name'] = $politician_select_result->kr_name;
            $response_data['en_name'] = $politician_select_result->en_name;
            $response_data['ch_name'] = $politician_select_result->ch_name;
            $response_data['sex'] = $politician_select_result->sex;
            $response_data['committee'] = $politician_select_result->committee;
            $response_data['office_number'] = $politician_select_result->office_number;
            $response_data['email'] = $politician_select_result->email;
            $response_data['birthday'] = (int)$politician_select_result->birthday;
            $response_data['history'] = $politician_select_result->history;
            $response_data['education'] = $politician_select_result->education;
            $response_data['soldier'] = $politician_select_result->soldier;
            $response_data['birth_area'] = $politician_select_result->birth_area;
            $response_data['twitter'] = $politician_select_result->twitter;
            $response_data['instagram'] = $politician_select_result->instagram;
            $response_data['blog'] = $politician_select_result->blog;
            $response_data['homepage'] = $politician_select_result->homepage;
            $response_data['facebook'] = $politician_select_result->facebook;
            $response_data['youtube'] = $politician_select_result->youtube;
            $response_data['profile_image_url'] = 'http://politicsking.com/files/images/politician_thumbnail/' . $politician_select_result->idx . '.jpg';

            $sql = "SELECT elect_do, elect_gun, elect_gu, start_day, end_day, progress_status, vote_score 
                FROM PoliticianGeneration where politician_idx = ?";

            $politician_generation_s_result = $this->db->query($sql, array($politician_idx))->row();

            $politician_generation_array = array();
            $start_day = $politician_generation_s_result->start_day;
            $end_day = $politician_generation_s_result->end_day;
            $elect_do = $politician_generation_s_result->elect_do;
            $elect_gun = $politician_generation_s_result->elect_gun;
            $elect_gu = $politician_generation_s_result->elect_gu;
            $vote_score = $politician_generation_s_result->vote_score;
            $progress_status = $politician_generation_s_result->progress_status;

            $politician_generation_array['start_day'] = (int)$start_day;
            $politician_generation_array['end_day'] = (int)$end_day;
            $politician_generation_array['elect_do'] = $elect_do;
            $politician_generation_array['elect_gun'] = $elect_gun;
            $politician_generation_array['elect_gu'] = $elect_gu;
            $politician_generation_array['vote_score'] = (double)$vote_score;
            $politician_generation_array['progress_status'] = $progress_status;

            $response_data['generation_info'] = $politician_generation_array;

            $sql = "SELECT party_idx, start_day, end_day
                FROM PoliticianPartyHistory where politician_idx = ? and end_day is NULL";

            $politician_party_history_s_result = $this->db->query($sql, array($politician_idx))->row();

            $party_array = array();
            $party_start_day = $politician_party_history_s_result->start_day;
            $party_end_day = $politician_party_history_s_result->end_day;
            $party_idx = $politician_party_history_s_result->party_idx;

            $sql = "SELECT `name` FROM Party where idx = ?";
            $party_s_result = $this->db->query($sql, array($party_idx))->row();

            $party_name = $party_s_result->name;

            $party_array['start_day'] = (int)$party_start_day;
            $party_array['end_day'] = (int)$party_end_day;
            $party_array['party_name'] = $party_name;

            $response_data['party_info'] = $party_array;

            return json_encode($response_data, JSON_UNESCAPED_UNICODE);
        }
    }

    // 정치인 관련 뉴스정보 요청
    // input : 정치인 인덱스
    // output : 뉴스제목, 뉴스날짜, 뉴스 링크 경로
    public function getNews($politician_idx)
    {
        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        $sql = "SELECT count(idx) as cnt FROM Politician";

        $result = $this->db->query($sql)->row();

        if ((int)$result->cnt < (int)$politician_idx or (int)$politician_idx < 0) {
            header("HTTP/1.1 400");
            return;
        }

        $sql = "SELECT * FROM News where politician_idx = ?";

        // 정치인 관련 뉴스 조회
        $politician_select_result = $this->db->query($sql, array($politician_idx))->result();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT * FROM News where politician_idx = $politician_idx";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql)
        );

        $result_num = count($politician_select_result);

        if ($result_num == 0) {
            header("HTTP/1.1 204 ");
            return;
        } else {
            $response_data['result_num'] = (int)$result_num;
            $news_data_array = array();

            foreach ($politician_select_result as $row) {
                $data_array = array();
                $data_array['politician_idx'] = $row->politician_idx;
                $data_array['title'] = $row->title;
                $data_array['date'] = $row->date;
                $data_array['news_link'] = $row->url;
                $data_array['category'] = $row->category;
                $data_array['thumbnail_image'] = $row->thumbnail;
                $data_array['content'] = $row->content;
                array_push($news_data_array, $data_array);
            }
            $response_data['data'] = $news_data_array;
        }
        return json_encode($response_data);
    }

    // 정치인 공약 정보 요청
    // input : 정치인 인덱스
    // output : 공약내용, 공약대수, 공약 이행 상태
    public function getPledgeInfo($politician_idx, $generation)
    {

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        $sql = "SELECT count(idx) as cnt FROM Politician";

        $result = $this->db->query($sql)->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT count(idx) as cnt FROM Politician";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql
            )
        );

        if ((int)$result->cnt < (int)$politician_idx or (int)$politician_idx < 0) {
            header("HTTP/1.1 400 ");
            return;
        }


        $sql = "SELECT idx FROM PoliticianGeneration where politician_idx = ? and generation = ?";

        // 정치인 대수 인덱스 가져오기
        $politician_select_result = $this->db->query($sql, array($politician_idx, $generation))->row();
        $politician_generation_idx = (int)($politician_select_result->idx);

        $sql = "SELECT * FROM PoliticianPledge where politician_generation_idx = ?";

        // 정치인 대수로 정치인 공약 정보를 가져온다.
        $politician_pledge_s_result = $this->db->query($sql, array($politician_generation_idx))->result();

        // 정치인 공약 지역 정보를 배열에 넣음 ex ) 가양1동,가양2동 등
        $pledge_area = array();
        foreach ($politician_pledge_s_result as $row) {
            array_push($pledge_area, $row->pledge_area);
        }

        // 공약 지역이 겹치는 데이터가 많아 중복을 제거한다.
        $pledge_area = array_unique($pledge_area);
        $pledge_area = $this->arraySort($pledge_area);

        // 공약 정보를 담을 배열
        $pledge_temp_data = array();

        // 공약 지역에 해당하는 공약 내용을 추가한다.
        // 공약지역:{
        //  공약1,
        //  공약2 와 같은 형태로 넣을거임
        // }
        for ($i = 0; $i < count($pledge_area); $i++) {

            $pledge_data = array();
            $content = array();
            $pledge_status = array();

            $sql = "SELECT * FROM PoliticianPledge where pledge_area = ? and politician_generation_idx = ?";

            $content_result = $this->db->query($sql, array($pledge_area[$i], $politician_generation_idx))->result();

            foreach ($content_result as $row) {
                array_push($content, $row->content);
                array_push($pledge_status, $row->pledge_implement_status);
            }
            $pledge_data['pledge_area'] = $pledge_area[$i];
            $pledge_data['content'] = $content;
            $pledge_data['pledge_status'] = $pledge_status;
            array_push($pledge_temp_data, $pledge_data);
        }
        $response_data['result'] = $pledge_temp_data;

        return json_encode($response_data);

    }

    // 정치인 북마크 여부 조회
    public function getBookmark($politician_idx, $token_data)
    {
        // 클라에게 보내줄 응답 데이터
        $response_data = array();
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        // 사용자 인덱스
        $user_idx = $token_data->idx;

        $sql = "SELECT * , count(*) as `count` FROM BookMark where 
                user_idx = ? and politician_idx = ?";

        // 좋아요 싫어요 정보조회
        $result = $this->db->query($sql, array($user_idx, $politician_idx))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT * , count(*) as `count` FROM BookMark where 
                user_idx = $user_idx and politician_idx = $politician_idx";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql,
                'id' => $token_data->id
            )
        );

        if ($result->count == 0) {
            header("HTTP/1.1 204 ");
            return;
        } else {
            $response_data['status'] = '현재 북마크중';
        }
        return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보 조회
    public function getUserEvaluation($politician_idx, $token_data)
    {
        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 사용자 인덱스
        $user_idx = $token_data->idx;
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        $sql = "SELECT * , count(*) as `count` FROM UserEvaluationBill where 
                user_idx = ? and politician_idx = ?";

        // 좋아요 싫어요 정보조회
        $result = $this->db->query($sql, array($user_idx, $politician_idx))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT * , count(*) as `count` FROM UserEvaluationBill where 
                user_idx = $user_idx and politician_idx = $politician_idx";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql,
                'id' => $token_data->id
            )
        );

        if ($result->count == 0) {
            header("HTTP/1.1 204 ");
        } else {
            $response_data['status'] = $result->status;
        }
        return json_encode($response_data);
    }

    // pdf 조회
    public function getPDF($politician_idx)
    {

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        $sql = "SELECT count(idx) as cnt FROM Politician";

        $result = $this->db->query($sql)->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT count(idx) as cnt FROM Politician";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql,
            )
        );

        if ((int)$result->cnt < (int)$politician_idx or (int)$politician_idx < 0) {
            header("HTTP/1.1 400 ");
            return;
        }

        $pdf_url = 'http://politicsking.com/files/pdf/' . $politician_idx . '.pdf';
        $response_data['pdf_url'] = $pdf_url;
        return json_encode($response_data);
    }

    // 정치인 북마크 클릭/해제
    public function postBookmarkModify($politician_idx, $token_data)
    {

        // 클라에게 보내줄 응답 데이터
        $response_data = array();

        // 유저 인덱스
        $user_idx = $token_data->idx;
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        $sql = "SELECT count(idx) as `count`  FROM BookMark where user_idx = ? and politician_idx = ?";

        // 사용자가 해당 정치인을 북마크 했는지 여부 보기
        $bookmark_select_result = $this->db->query($sql, array($user_idx, $politician_idx))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT count(idx) as `count`  FROM BookMark where user_idx = $user_idx and politician_idx = $politician_idx";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql,
                'id' => $token_data->id
            )
        );

        // 해당 정치인을 북마크를 하고있었는데, 북마크 삭제 요청이 들어옴
        if ($bookmark_select_result->count == 1) {
            $sql = "DELETE FROM BookMark WHERE user_idx = ? and politician_idx = ?";
            $this->db->query($sql, array($user_idx, $politician_idx));
            $response_data['result'] = '북마크 삭제';
        } // 해당 정치인을 북마크를 안하고 있었는데, 북마크 추가 요청이 들어옴
        else {
            $sql = "INSERT INTO BookMark VALUES (null, ?, null, ?, NOW(),NOW(),NOW())";

            $this->db->query($sql, array($user_idx, $politician_idx));
            $response_data['result'] = '북마크 추가';
        }
        header("HTTP/1.1 201 ");
        return json_encode($response_data);
    }

    // 정치인 좋아요 싫어요 정보수정
    public function postUserEvaluation($politician_idx, $like_status, $token_data)
    {
        // 클라에게 보내줄 응답 데이터
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        $response_data = array();

        // 클라이언트가 좋아요를 요청한경우
        if ($like_status == "like") {
            $status = 1;
        } // 클라이언트가 싫어요를 요청한경우
        else {
            $status = 0;
        }

        // 사용자 인덱스
        $user_idx = $token_data->idx;

        $sql = "SELECT count(idx) as `count`  FROM UserEvaluationBill where 
                user_idx = ? and politician_idx = ?";

        // 사용자의 정치인에 대한 좋아요 데이터가 있는지 확인
        $bookmark_select_result = $this->db->query($sql, array($user_idx, $politician_idx))->row();

        // 사용자의 sql 및 id 등 로그 기록하기
        $log_sql = "SELECT count(idx) as `count`  FROM UserEvaluationBill where 
                user_idx = $user_idx and politician_idx = $politician_idx";
        $this->option_model->logRecode(
            array(
                'sql' => $log_sql,
                'id' => $token_data->id
            )
        );

        // 좋아요 싫어요 데이터가 있는 경우
        if ($bookmark_select_result->count == 1) {

            $sql = "select *, count(*) as `count` from UserEvaluationBill where 
                user_idx = ? and politician_idx = ?";

            // 사용자의 정치인 좋아요에 대한 데이터가 있는가?
            $result = $this->db->query($sql, array($user_idx, $politician_idx))->row();

            // 데이터가 같은 경우 - 데이터 삭제
            if ($result->count == 1) {

                $current_status = (int)$result->status;

                if ($current_status == $status) {
                    // 좋아요 해제
                    $sql = "delete from UserEvaluationBill where user_idx= $user_idx and politician_idx= $politician_idx";
                    $this->db->query($sql, array($user_idx, $politician_idx));
                    if ($current_status == 1) {
                        $response_data['result'] = "좋아요 해제";
                    } // 싫어요 해제
                    else {
                        $response_data['result'] = "싫어요 해제";
                    }
                } else {
                    // 클라이언트가 좋아요 요청
                    if ($status == 1) {
                        $sql = "update UserEvaluationBill set status=1 where user_idx= ? and politician_idx= ?";
                        $this->db->query($sql, array($user_idx, $politician_idx));
                        $response_data['result'] = "좋아요 활성화 싫어요 해제";
                    } else if ($status == 0) {
                        $sql = "update UserEvaluationBill set status=0 where user_idx= ? and politician_idx= ?";
                        $this->db->query($sql, array($user_idx, $politician_idx));
                        $response_data['result'] = "좋아요 해제 싫어요 활성화";
                    }
                }
            }
        } // 좋아요 싫어요 데이터가 없는 경우
        else {
            $sql = "INSERT INTO UserEvaluationBill VALUES (null, null ,?,?,?)";

            $this->db->query($sql, array($politician_idx, $user_idx, $status));
            if ($status == 1) {
                $response_data['result'] = "좋아요 활성화";
            } else {
                $response_data['result'] = "싫어요 활성화";
            }
        }

        header("HTTP/1.1 201 ");
        return json_encode($response_data);
    }

    // 정치인 랜덤 카드 덱 만들기
    public function postMakeRandomCard()
    {

        // 대수
        $generation = 20;

        $sql = "SELECT politician_idx FROM PoliticianGeneration where generation = ?";

        $result = $this->db->query($sql, array($generation))->result();

        $temp = array();

        for ($i = 0; $i < 1000; $i++) {

            $random_idx_array = array();

            foreach ($result as $row) {
                array_push($random_idx_array, (int)$row->politician_idx);
                shuffle($random_idx_array);
            }
            $temp[$i] = $random_idx_array;

            $card_number_str = "";

            for ($j = 0; $j < count($temp[$i]); $j++) {
                $card_number_str = $card_number_str . $temp[$i][$j] . ",";
            }

            $sql = "INSERT INTO RandomCard VALUES (null, $generation,'$card_number_str')";
            $this->db->query($sql, array($generation, $card_number_str));
        }
    }

    // 정치인 필터 기능 및 검색 결과
    public function getFilterCard($token_data, $generation_array, $party_array, $card_num, $page)
    {
        /** 클라이언트에서 디폴트로 항상 [대수 - 20대]가 선택 되어있는 상태로 전달을 해준다. */

        /**
         * @var  $response_data = 클라이언트에게 응답해줄 값 - 응답값은 항상 json 형태로 반환해줘야함..
         * @var  $token_data = 클라이언트에게 전달받은 토큰 정보값. - 로그인 여부를 확인할때 사용한다. ex) token_data->idx와 같이 사용 할 수 있다.
         * @var  $generation_array = 클라이언트에게 전달받은 대수 정보값. - "20,19" 와 같은 문자열 형태로 들어온다.
         * @var  $party_array = 클라이언트에게 전달받은 정당 정보값. - "더불어민주당,정의당" 와 같은 문자열 형태로 들어온다.
         * @var  $card_num = 클라이언트가 요청한 카드의 개수. - 반환시 card_num개를 반환해줘야함..
         * @var  $page = 클라이언트에게 요청한 페이지. - 요청한 페이지에 맞는 카드의 정보를 반환해줘야함.
         */

        $response_data = array();

        /** 사용자가 로그인 했을때만 북마크 정보 저장 / 카드정보에 북마크 정보를 담아서 보내주기 위함.*/
        if ($token_data->idx != '토큰실패') {
            // jwt 토큰에서 받은 아이디
            $user_idx = $token_data->idx;

            //사용자의 인덱스로 찾은 정치인 북마크 인덱스
            $sql = "select politician_idx from BookMark where user_idx = ?";
            $book_mark_select_result = $this->db->query($sql, array($user_idx))->result();

            // 배열에 정치인 북마크 인덱스 담기
            for ($i = 0; $i < count($book_mark_select_result); $i++) {
                $book_mark_array[$i] = $book_mark_select_result[$i]->politician_idx;
            }
        }

        /** 필터 정보 값만 있는 경우 */
        if ($generation_array[0] != "" and $party_array[0] == "") {

            /** 요청값 (대수)로 정치인 인덱스를 찾는다. : 정치인의 정보를 얻기 위해서 찾음 */
            // 정치인의 인덱스가 담길 공간
            $politician_idx_array = array();

            // 대수 조건과 일치하는 정치인 인덱스를 찾아 politician_idx_array 변수에 배열 형태로 넣기
            // 정치인에 대한 정보를 찾을때 사용할 것임
            for ($i = 0; $i < count($generation_array); $i++) {

                $sql = "SELECT politician_idx FROM PoliticianGeneration WHERE generation = ?";
                $politician_idx_result = $this->db->query($sql, array($generation_array[$i]))->result();
                foreach ($politician_idx_result as $row)
                    array_push($politician_idx_array, (int)$row->politician_idx);

                // 대수 조건과 일치하는 정치인이 없을때 제공해줄 컨텐츠가 없다는 응답값을 클라에게 보낸다.
                if (count($politician_idx_array) == 0) {
                    header("HTTP/1.1 204 ");
                    return;
                }

            }

            // 중복된 인덱스를 제거 하는 과정 : 정치인에 대한 정보를 찾을때 인덱스가 여러개일 필요는 없다.
            $politician_idx_array = array_unique($politician_idx_array);
            $politician_idx_array = $this->arraySort($politician_idx_array);
            // 총 카드 갯수에 따라 전체 페이지 수 구하기 / 카드의 개수가 클라이언트가 요청한 카드보다 적다면 총 페이지는 1로 정의함
            $total_page = (int)ceil(count($politician_idx_array) / $card_num);
            if ($total_page == 0) $total_page = 1;

            // 사용자가 총 페이지수 이상의 페이지를 요청했을때 컨텐츠가 제공되지 않는다는 응답값을 보내준다.
            if ($page > $total_page) {
                header("HTTP/1.1 204 ");
                return;
            }


            /**
             * 클라이언트가 요청한 카드의 개수 만큼 카드를 만드는 과정
             * @var $card_list = 카드 한장, 한장의 카드 정보가 담길 리스트
             * @var $card_data = 카드의 정보가 담길 변수
             * @var $generation = 정치인의 대수 정보 ex) "20,19" 등과 같이 저장된다.
             */
            $card_list = array();
            $i = $card_num * ($page - 1);
            while (true) {
                $card_data = array();
                $generation = "";

                // 1번에서 찾은 정치인 인덱스 배열 갯수 보다 정치인 카드 갯수가 많다면 반복을 종료
                if ($i >= count($politician_idx_array))
                    break;

                // 정치인의 대수 정보를 찾는 작업
                $sql = "SELECT generation FROM PoliticianGeneration where politician_idx = ? order by generation desc";
                $generation_array = $this->db->query($sql, array($politician_idx_array[$i]))->result();
                foreach ($generation_array as $row)
                    $generation = $generation . $row->generation . ',';
                $generation = substr($generation, 0, -1);

                // 정치인 인덱스로 정치인에 대한 정보 찾기
                $sql = "SELECT * FROM Politician WHERE idx = ?";
                $politician_info = $this->db->query($sql, array($politician_idx_array[$i]))->row();

                // 사용자가 로그인했을때만 북마크 여부를 반환해줌
                if ($token_data->idx != "토큰실패") {
                    if (in_array($politician_idx_array[$i], $book_mark_array)) {
                        $card_data['bookmark'] = true;
                    } else {
                        $card_data['bookmark'] = false;
                    }
                }

                // 정치인의 현재 정당의 인덱스를 가져온다. 정치인의 정당을 보여주기 위함
                $sql = "SELECT party_idx FROM PoliticianPartyHistory where politician_idx = ? and end_day is ?";
                $party_idx = $this->db->query($sql, array($politician_idx_array[$i], null))->row()->party_idx;

                // 정당인덱스가 없다면 - DB에 값이 null인 경우임
                if ($party_idx == null) {
                    $party_name = null;
                } // 정당인덱스가 있다면 정당의 이름정보를 가져와서 party_name을 초기화 시켜준다.
                else {
                    $sql = "SELECT name FROM Party where idx = ?";
                    $party_name = $this->db->query($sql, array($party_idx))->row()->name;
                }

                $card_data['kr_name'] = $politician_info->kr_name;
                $card_data['committee'] = $politician_info->committee;
                $card_data['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_info->idx . ".jpg";
                $card_data['politician_idx'] = (int)$politician_idx_array[$i];
                $card_data['party_name'] = $party_name;
                $card_data['generation'] = $generation;

                $i = $i + 1;
                array_push($card_list, $card_data);
                if (count($card_list) == $card_num) {
                    break;
                }
            }

            $response_data['card_num'] = (int)count($card_list);
            $response_data['current_page'] = $page;
            $response_data['total_page'] = $total_page;
            $response_data['card_list'] = $card_list;
            return json_encode($response_data);

        } /**
         * 대수와 정당 값을 모두 받았을때
         * ex) 대수 : 20, / 소속정당 : 더불어민주당,국민의당.
         * 20대에 더불어민주당을 거쳤던 의원
         * 20대에 국민의당을 거쳤던 의원에 대해서 정보를 제공 해주어야한다.
         */
        else if ($party_array[0] != "" and $generation_array[0] != "") {

            /** 요청값 (대수)로 정치인 대수 정보를 찾는다 */
            // 정치인과 정당 인덱스가 담길 공간
            $politician_idx_array = array();
            $party_idx_array = array();
            $real_politician_idx_array = array();

            // 대수 조건과 일치하는 정치인 인덱스를 찾기
            $sql = "SELECT politician_idx FROM PoliticianGeneration WHERE generation IN ?";
            $politician_result = $this->db->query($sql, array($generation_array))->result();

            // 정당 이름 조건과 일치하는 정당인덱스 찾기
            $sql = "SELECT idx FROM Party WHERE name IN ?";
            $party_result = $this->db->query($sql, array($party_array))->result();

            // $politician_idx_array에 정치인 인덱스 저장
            for ($i = 0; $i < count($politician_result); $i++)
                array_push($politician_idx_array, (int)$politician_result[$i]->politician_idx);

            // $party_idx_array 정당 인덱스 저장
            for ($i = 0; $i < count($party_result); $i++)
                array_push($party_idx_array, (int)$party_result[$i]->idx);

            // 정치인 히스토리 테이블에서 정치인 인덱스에 해당하는 정당이 있었는지 찾아야함.
            foreach ($politician_idx_array as $po_idx) {
                foreach ($party_idx_array as $pa_idx) {
                    $sql = "SELECT politician_idx FROM PoliticianPartyHistory WHERE politician_idx = ? and party_idx = ?";
                    $po_party_history_result = $this->db->query($sql, array($po_idx, $pa_idx))->row();
                    if ($po_party_history_result != null) {
                        array_push($real_politician_idx_array, (int)$po_party_history_result->politician_idx);
                    }
                }
            }

            $real_politician_idx_array = array_unique($real_politician_idx_array);
            $real_politician_idx_array = $this->arraySort($real_politician_idx_array);

            // 총 카드 갯수에 따라 전체 페이지 수 구하기 / 카드의 개수가 클라이언트가 요청한 카드보다 적다면 총 페이지는 1로 정의함
            $total_page = (int)ceil(count($real_politician_idx_array) / $card_num);
            if ($total_page == 0) $total_page = 1;
            // 사용자가 총 페이지수 이상의 페이지를 요청했을때 컨텐츠가 제공되지 않는다는 응답값을 보내준다.
            if ($page > $total_page) {
                header("HTTP/1.1 204 ");
                return;
            }

            /**
             * 클라이언트가 요청한 카드의 개수 만큼 카드를 만드는 과정
             * @var $card_list = 카드 한장, 한장의 카드 정보가 담길 리스트
             * @var $card_data = 카드의 정보가 담길 변수
             * @var $generation = 정치인의 대수 정보 ex) "20,19" 등과 같이 저장된다.
             */
            $card_list = array();
            $i = $card_num * ($page - 1);
            while (true) {
                $card_data = array();
                // 정치인 카드 갯수가 모자란다면 반복을 종료
                if ($i >= count($real_politician_idx_array)) {
                    break;
                }

                // 정치인 인덱스로 정치인에 대한 정보 찾기
                $sql = "SELECT * FROM Politician WHERE idx = ?";
                $politician_info = $this->db->query($sql, array((int)$real_politician_idx_array[$i]))->row();

                // 정치인이 요청받은 정당에 포함되는지 찾기
                $sql = "SELECT politician_idx FROM PoliticianPartyHistory WHERE politician_idx = ? and party_idx IN ?";
                $politician_party_history_result = $this->db->query($sql, array((int)$real_politician_idx_array[$i], $party_idx_array))->row();

                // 정치인 인덱스로 정치인에 정당 인덱스 찾기
                $sql = "SELECT party_idx FROM PoliticianPartyHistory WHERE politician_idx = ? and end_day is null";
                $politician_party_history_result = $this->db->query($sql, array($politician_party_history_result->politician_idx))->row();

                // 정당 인덱스로 정당 이름 찾기
                $sql = "SELECT `name` FROM Party WHERE idx = ?";
                $politician_party_name = $this->db->query($sql, array($politician_party_history_result->party_idx))->row()->name;

                // 정치인 정보로 해당 정치인의 대수정보 구하기
                $sql = "SELECT generation FROM PoliticianGeneration WHERE politician_idx = ? order by generation desc";
                $politician_generation_array = $this->db->query($sql, array((int)$real_politician_idx_array[$i]))->result();

                // 사용자가 로그인했을때만 북마크 여부를 보여준다.
                if ($token_data->idx != "토큰실패") {
                    if (in_array($real_politician_idx_array[$i], $book_mark_array)) {
                        $card_data['bookmark'] = true;
                    } else {
                        $card_data['bookmark'] = false;
                    }
                }

                $generation = "";
                foreach ($politician_generation_array as $row)
                    $generation = $generation . $row->generation . ',';
                $generation = substr($generation, 0, -1);

                $card_data['kr_name'] = $politician_info->kr_name;
                $card_data['committee'] = $politician_info->committee;
                $card_data['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_info->idx . ".jpg";
                $card_data['politician_idx'] = (int)$real_politician_idx_array[$i];
                $card_data['party_name'] = $politician_party_name;
                $card_data['generation'] = $generation;

                $i = $i + 1;
                array_push($card_list, $card_data);
                if (count($card_list) == $card_num) {
                    break;
                }
            }
            if (count($card_list) == 0) {
                header("HTTP/1.1 204 ");
                return;
            }
            $response_data['card_num'] = (int)count($card_list);
            $response_data['current_page'] = (int)$page;
            $response_data['total_page'] = (int)$total_page;
            $response_data['card_list'] = $card_list;

            return json_encode($response_data);
        }
    }

    // 정치인 검색 기능
    public function getKeywordSearch($token_data, $keyword, $page, $card_num)
    {

        /** 사용자가 로그인 했을때만 북마크 정보 저장 / 카드정보에 북마크 정보를 담아서 보내주기 위함.*/
        if ($token_data->idx != '토큰실패') {
            // jwt 토큰에서 받은 아이디
            $user_idx = $token_data->idx;

            //사용자의 인덱스로 찾은 정치인 북마크 인덱스
            $sql = "select politician_idx from BookMark where user_idx = ?";
            $book_mark_select_result = $this->db->query($sql, array($user_idx))->result();

            // 배열에 정치인 북마크 인덱스 담기
            for ($i = 0; $i < count($book_mark_select_result); $i++) {
                $book_mark_array[$i] = $book_mark_select_result[$i]->politician_idx;
            }
        }

        $sql = "select * from Politician where kr_name LIKE ?";
        $politician_s_result = $this->db->query($sql, array('%' . $keyword . '%'))->result();

        $politician_idx_array = array();

        foreach ($politician_s_result as $row) {
            array_push($politician_idx_array, $row->idx);
        }


        // 총 카드 갯수에 따라 전체 페이지 수 구하기 / 카드의 개수가 클라이언트가 요청한 카드보다 적다면 총 페이지는 1로 정의함
        $total_page = (int)floor(count($politician_idx_array) / $card_num);
        if ($total_page == 0) $total_page = 1;

        /**
         * 클라이언트가 요청한 카드의 개수 만큼 카드를 만드는 과정
         * @var $card_list = 카드 한장, 한장의 카드 정보가 담길 리스트
         * @var $card_data = 카드의 정보가 담길 변수
         * @var $generation = 정치인의 대수 정보 ex) "20,19" 등과 같이 저장된다.
         */
        $card_list = array();

        for ($i = $card_num * ($page - 1); $i < $card_num * ($page - 1) + $card_num; $i++) {
            $card_data = array();
            $generation = "";

            if ($i >= count($politician_idx_array))
                break;

            // 정치인의 대수 정보를 찾는 작업
            $sql = "SELECT generation FROM PoliticianGeneration where politician_idx = ? order by generation desc";
            $generation_array = $this->db->query($sql, array($politician_idx_array[$i]))->result();
            foreach ($generation_array as $row)
                $generation = $generation . $row->generation . ',';
            $generation = substr($generation, 0, -1);

            // 정치인 인덱스로 정치인에 대한 정보 찾기
            $sql = "SELECT * FROM Politician WHERE idx = ?";
            $politician_info = $this->db->query($sql, array($politician_idx_array[$i]))->row();

            // 사용자가 로그인했을때만 북마크 여부를 반환해줌
            if ($token_data->idx != "토큰실패") {
                if (in_array($politician_idx_array[$i], $book_mark_array)) {
                    $card_data['bookmark'] = true;
                } else {
                    $card_data['bookmark'] = false;
                }
            }

            // 정치인의 현재 정당의 인덱스를 가져온다. 정치인의 정당을 보여주기 위함
            $sql = "SELECT party_idx FROM PoliticianPartyHistory where politician_idx = ? and end_day is ?";
            $party_idx = $this->db->query($sql, array($politician_idx_array[$i], null))->row()->party_idx;

            // 정당인덱스가 없다면 - DB에 값이 null인 경우임
            if ($party_idx == null) {
                $party_name = null;
            } // 정당인덱스가 있다면 정당의 이름정보를 가져와서 party_name을 초기화 시켜준다.
            else {
                $sql = "SELECT name FROM Party where idx = ?";
                $party_name = $this->db->query($sql, array($party_idx))->row()->name;
            }

            $card_data['kr_name'] = $politician_info->kr_name;
            $card_data['committee'] = $politician_info->committee;
            $card_data['image_url'] = "http://politicsking.com/files/images/politician_thumbnail/" . (string)$politician_info->idx . ".jpg";
            $card_data['politician_idx'] = (int)$politician_idx_array[$i];
            $card_data['party_name'] = $party_name;
            $card_data['generation'] = $generation;

            array_push($card_list, $card_data);

        }
        if (count($card_list) == 0) {
            // 클라이언트가 첫 페이지 요청할때, 덱 번호가 정해져 있지 않아서 -1값으로 요청이 들어온다.
            header("HTTP/1.1 204 ");
            return;
        }

        $response_data['card_num'] = (int)count($card_list);
        $response_data['current_page'] = (int)$page;
        $response_data['total_page'] = (int)$total_page;
        $response_data['card_list'] = $card_list;
        if (count($card_list) == 0) {
            header("HTTP/1.1 204 ");
            return;
        }
        return json_encode($response_data);
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
