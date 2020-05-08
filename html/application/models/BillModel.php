<?php
/*
 * 법안 모아보기 법안 상세보기 등
 * 법안에 관련된 DB를 참조 하는데 사용하는 모델
 * */

class BillModel extends CI_Model
{
    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }


    /*법안 모아보기 페이지 데이터
    /출력데이터
{
    "total": 10,                                                            //총 페이지수 페이징을 위함
    "info": [
        {
            "bill_idx": "14",                                               //법안의 인덱스
            "name": "name 014",                                               //법안명
            "progress_status": "progress_status 014",                       //법안 진행 상태
            "proposal_date": "2020-04-29 01:49:06",                         //제안 날짜
            "committee_idx": "idx14",                                       //위원회 idx
            "committee_name": "name 014",                                   //위원회 이름
            "proclamation_number":"123123",                                 //공포번호
            "proposer": [                                                   //발의에 참여한 사람들 리스트
                {
                    "idx": "14",                                            //정치인 idx
                    "kr_name": "kr_name 014",                               //정치인 이름
                    "representative": "14",                                 //1이면 대표발의 0이면 공동발의
                    "party_idx": "14",                                      //정당idx
                    "party_name": "party_name 014"                          //정당이름
               }
            ]
        }, ...........
    }
 }
     * */
    function billPageList($index)
    {
        //모아보기 메인 == index로 들어왔을때
        if ($index == NULL) {
            $index = 0;
        } else {
            $index = ($index - 1) * 10;
        }
        $result = array();
        //법안 모아보기 정보
        $bill_info = $this->db->query("select  idx,name,committee_idx,progress_status,proposal_date,proclamation_number from Bill order by idx desc limit $index, 10")->result();
        //페이징을 위한 총 페이지수
        $bill_total_rows = $this->db->query("select count(idx) as total from Bill ")->row();
        $result['total_page'] = ceil($bill_total_rows->total / 10);
        $bill_array = array();
        foreach ($bill_info as $row) {

            $bill_data = array();
            $bill_data['bill_idx'] = (int)$row->idx;
            $bill_data['name'] = $row->name;
            $bill_data['progress_status'] = $row->progress_status;
            $bill_data['proposal_date'] = $row->proposal_date;
            $bill_data['proclamation_number'] = (int)$row->proclamation_number;
            //법안을 발의한 위원회
            $bill_data['committee_idx'] = (int)$row->committee_idx;
            //위원회 idx로 위원회 이름 얻기
            $committee = $this->db->query("select name from Committee where idx =$row->committee_idx")->row();
            $bill_data['committee_name'] = $committee->name;


            //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
            $bill_data['proposers'] = $this->billIndexToPoliticians($row->idx);


            array_push($bill_array, $bill_data);

        }
        $result['info'] = $bill_array;
        return json_encode($result);
    }

    //법안 상세보기에 들어갈 데이터
    function billInfoData($index)
    {
        $result = array();
        //데이터 1개만 가져옴 나중에 row로 바꿔야할듯
        $bill_rows = $this->db->query("select * from Bill where idx=$index")->result();
        foreach ($bill_rows as $row) {
            $bill_data = array();
            $bill_data['idx'] = $row->idx;
            $bill_data['bill_name'] = $row->name;
            //위원회 idx
            $bill_data['committee_idx'] = $row->committee_idx;
            //위원회 idx로 위원회 이름 얻기
            $committee = $this->db->query("select name from Committee where idx =$row->committee_idx")->row();
            $bill_data['committee_name'] = $committee->name;

            //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
            $bill_data['proposers'] = $this->billIndexToPoliticians($row->idx);
            //법안인덱스로 해당 법안에 참여한 인원은 몇명이고 찬성,반대,기권,불참한 정치인들의 idx를 가져옴
            $result['bill_Vote'] = $this->billIndexToBillVote($row->idx);
            //예고기간 시작,종료
            $bill_data['notice_period_start'] = $row->notice_period_start;
            $bill_data['notice_period_end'] = $row->notice_period_end;
            //진행상태
            $bill_data['progress_status'] = $row->progress_status;
            //회부일
            $bill_data['reject_day'] = $row->reject_day;
            //상정일
            $bill_data['pass_day'] = $row->pass_day;
            //처리일
            $bill_data['disposal_day'] = $row->disposal_day;
            //공포일
            $bill_data['proclamation_day'] = $row->proclamation_day;
            //원문 hwp링크주소
            $bill_data['hwp_url'] = $row->hwp_url;
            //원문 pdf링크주소
            $bill_data['pdf_url'] = $row->pdf_url;
            //공포 법률 링크주소
            $bill_data['proclamation_law_url'] = $row->proclamation_law_url;
            //요약내용
            $bill_data['summary_content'] = $row->summary_content;
            //공포번호
            $bill_data['proclamation_number'] = (int)$row->proclamation_number;
            //제안날짜
            $bill_data['proposal_date'] = $row->proposal_date;
            //법안번호
            $bill_data['bill_number'] = $row->bill_number;

            $result['bill_info'] = $bill_data;


        }
        return json_encode($result);

    }

    //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
    private function billIndexToPoliticians($bill_idx)
    {
        $proposer = $this->db->query("select politician_idx,representative from Proposer where bill_idx=$bill_idx")->result();
        $proposer_array = array();
        foreach ($proposer as $proposer_row) {
            $proposer_data = array();
            $politicians = $this->db->query("select idx,party_idx,kr_name from Politician where idx=$proposer_row->politician_idx")->row();
            $proposer_data['idx'] = (int)$politicians->idx;
            $proposer_data['kr_name'] = $politicians->kr_name;
            $proposer_data['representative'] = (int)$proposer_row->representative;
            //정당인덱스로 정당 이름 찾기
            $party = $this->db->query("select idx,party_name from Party where idx=$politicians->party_idx")->row();
            $proposer_data['party_idx'] = (int)$party->idx;
            $proposer_data['party_name'] = $party->party_name;

            array_push($proposer_array, $proposer_data);
        }
        return $proposer_array;
    }


    //법안인덱스로 해당 법안에 참여한 인원은 몇명이고 찬성,반대,기권,불참한 정치인들의 idx를 가져옴
    private function billIndexToBillVote($index)
    {
        $result = array();
//        $total_array=array();
        $vote_rows = $this->db->query("select politician_idx,vote_status from BillVote where bill_idx=$index")->result();
        $result['total'] = count($vote_rows);

        //찬성한 사람들을 담는 배열
        $agreement_array = array();
        //반대한 사람들을 담는 배열
        $opposition_array = array();
        //기권한 사람들을 담는 배열
        $abstention_array = array();
        //불참한 사람들을 담는 배열
        $absence_array = array();
        foreach ($vote_rows as $row) {
            if ($row->vote_status == 0) {
                $politician_idx = (int)$row->politician_idx;
                array_push($agreement_array, $this->votePerson($politician_idx));

            } else if ($row->vote_status == 1) {
                $politician_idx = (int)$row->politician_idx;
                array_push($opposition_array, $this->votePerson($politician_idx));
            } else if ($row->vote_status == 2) {
                $politician_idx = (int)$row->politician_idx;
                array_push($abstention_array, $this->votePerson($politician_idx));
            } else {
                $politician_idx = (int)$row->politician_idx;
                array_push($absence_array, $this->votePerson($politician_idx));
            }

        }
        $result['agreement'] = $agreement_array;
        $result['opposition'] = $opposition_array;
        $result['abstention'] = $abstention_array;
        $result['absence'] = $absence_array;

        return $result;


    }


    //input으로 찬성,반대,기권,불참 한 사람들의 배열이 들어옴
    //해당 인덱스를 가지고 의원의 이름 , 정당 을 반환
    private function votePerson($index)
    {
        $politician_row = $this->db->query("select party_idx,kr_name from Politician where idx=$index")->row();
        $data['idx'] = (int)$index;
        $data['party_idx'] = (int)$politician_row->party_idx;
        $data['kr_name'] = $politician_row->kr_name;
        $party_name = $this->db->query("select party_name from Party where idx=" . $data['party_idx'])->row();
        $data['party_name'] = $party_name->party_name;
        return $data;
    }

    //법안에 대한 좋아요 싫어요 보여주는 메소드
    //나중에 토큰값으로 사용자 확인후 사용자가 좋아요를 눌렀는지 싫어요를 눌렀는지 서버에서 판단해서 반환값 보내야함
    public function billUserStatus($index)
    {
        $agreement_count = $this->db->query("select count(bill_idx) as agreement from UserEvaluationBill where bill_idx =$index and status =0")->row();
        $opposition_count = $this->db->query("select count(bill_idx) as opposition from UserEvaluationBill where bill_idx =$index and status =1")->row();
        $result = array();
        $result['agreement_total'] = (int)$agreement_count->agreement;
        $result['opposition_total'] = (int)$opposition_count->opposition;
        $result['user_check'] = '나중에 사용자 정보 확인해서 사용자가 찬성했는지 반대했는지 아무것도 누르지 않았는지 알려줘야함';
        return json_encode($result);

    }

    //법안 상세보기에 들어가면 댓글을 볼수있음
    //찬성에 쓴 댓글 , 반대에 쓴 댓글
    //해당 댓글에 달린 답글갯수
    public function billCommentList($index, $comment_page, $status)
    {
        if ($status == 'agreement') {
            $status = 0;
        } else {
            $status = 1;
        }
        $page = ($comment_page - 1) * 10;
        $rows = $this->db->query("select * from Comment where bill_idx=$index and status=$status order by create_at asc limit $page , 10 ")->result();
        $sub_comment_array = array();
        foreach ($rows as $row) {
            //댓글 목록
            $data['user_idx'] = (int)$row->user_idx;
            $data['nick_name'] = $this->db->query("select * from User where idx=$row->user_idx")->row()->nick_name;
            $data['comment_idx'] = (int)$row->idx;
            $data['content'] = $row->content;
            $data['create_at'] = $row->create_at;
            //댓글에 대한 좋아요와 싫어요 갯수 and (사용자가 좋아요 or 싫어요를 클릭 했는지 여부)
            $data['agreement'] = (int)$this->db->query("select count(idx) as count from CommentRating where comment_idx=$row->idx and status=0")->row()->count;
            $data['opposition'] = (int)$this->db->query("select count(idx) as count from CommentRating where comment_idx=$row->idx and status=1 ")->row()->count;
            //대댓글이 몇개있는지 카운트로 알려줌
            $data['child'] = (int)$this->db->query("select count(idx) as count from SubComment where comment_idx=$row->idx")->row()->count;

            array_push($sub_comment_array, $data);

        }
        $result = array();
        //페이징  (총 페이지 수)
        $result['comment_total_page'] = ceil($this->db->query("select count(idx) as count from Comment where bill_idx=$index and status=$status")->row()->count / 10);
        $result['comment_list'] = $sub_comment_array;


        return json_encode($result);


    }

    //대댓글에 대한 정보 and 페이징
    public function billSubCommentList($index, $sub_comment_page)
    {
        //페이지는 무조건 1부터시작
        $sub_comment_page = ($sub_comment_page - 1) * 10;
        $rows = $this->db->query("select * from SubComment where comment_idx=$index  order by create_at asc limit $sub_comment_page , 10")->result();
        $result_array = array();
        $json_tmp = array();
        foreach ($rows as $row) {
            $data['user_idx'] = (int)$row->user_idx;
            $data['sub_comment_idx'] = (int)$row->idx;
            $data['user_nick_name'] = $this->db->query("select nick_name from User where idx=$row->user_idx")->row()->nick_name;
            $data['content'] = $row->content;
            $data['agreement'] = (int)$this->db->query("select count(idx) as count from CommentRating where sub_comment_idx=$row->idx and status=0")->row()->count;
            $data['opposition'] = (int)$this->db->query("select count(idx) as count from CommentRating where sub_comment_idx=$row->idx and status=1 ")->row()->count;
            //답글에 대한 답글일경우 부모 유저를 링크해준다
            if ($row->parent_user_idx != null) {
                $data['parent_user_idx'] = (int)$row->parent_user_idx;
                $data['parent_user_nick_name'] = $this->db->query("select nick_name from User where idx=$row->parent_user_idx")->row()->nick_name;

            }
            array_push($json_tmp, $data);
        }
        $result_array['comment'] = $json_tmp;
        $next = $this->db->query("select count(idx) as count from SubComment  where comment_idx=$index ")->row();
        if (($sub_comment_page + 1) * 10 < $next->count) {
            $result_array['next_page'] = true;
        } else {

            $result_array['next_page'] = false;
        }
        return json_encode($result_array);
    }

    //사용자가 법안에 대해 댓글을 작성할 경우
    public function billCommentWrite($bill_idx, $content, $status)
    {
        $result = 0;
        if ($status == 'agreement') {
            $this->db->query("insert into Comment(user_idx,bill_idx,content,create_at,status) values (1,$bill_idx,'$content',NOW(),0)");

        } else if ($status == 'opposition') {
            $this->db->query("insert into Comment(user_idx,bill_idx,content,create_at,status) values (1,$bill_idx,'$content',NOW(),1)");

        }
        $result_json = array();
        $comment_idx = $this->db->insert_id();
        $result_json['comment_idx'] = (int)$comment_idx;


        return json_encode($result_json);
    }
    //사용자가 댓글에 대한 답글을 작성 할 경우 (부보 인덱스가 없는경우)
    // or
    // 사용자가 답글에 대한 답글을 작성 할 경우 (부모 인덱스가 있는경우)
    public function billSubCommmentWrite($comment_idx, $content, $parent_idx)
    {
        if ($parent_idx == null) {
            $this->db->query("insert into SubComment(comment_idx,user_idx,content,create_at) values ($comment_idx,1,'$content',NOW())");
        } else {
            $this->db->query("insert into SubComment(comment_idx,user_idx,content,parent_user_idx,create_at) values ($comment_idx,1,'$content',$parent_idx,NOW())");

        }
        $result = array();
        $result['sub_comment_idx'] = $this->db->insert_id();
        return json_encode($result);
    }

    //사용자가 법안에 대해 좋아요 혹은 싫어요를 클릭함
    //사용자가 해당 법안에 대해 처음 좋아요 , 싫어요 클릭했을시에 row 생성
    //사용자가 좋아요 - > 싫어요 또는 싫어요 - > 좋아요 를 클릭했을시에 row 업데이트
    //사용자가 좋아요 해제 , 싫어요 해제  클릭시 row 삭제
    public function billEvaluationWrite($bill_idx, $status)
    {
        $result = $this->db->query("select count(*) as 'count' from UserEvaluationBill where bill_idx=$bill_idx")->row();
        //이미 데이터가 있는 경우
        if ($result->count >= 1) {
            if ($status == 'agreement') {
                $result = $this->db->query("update UserEvaluationBill set status=0 where user_idx=1 and bill_idx=$bill_idx");
            } else if ($status == 'opposition') {
                $result = $this->db->query("update UserEvaluationBill set status=1 where user_idx=1 and bill_idx=$bill_idx");
            } else {
                $result = $this->db->query("delete from UserEvaluationBill where user_idx=1 and bill_idx=$bill_idx");

            }
        } //처음 클릭 하는경우
        else {
            if ($status == 'agreement') {
                $result = $this->db->query("insert into UserEvaluationBill(bill_idx,user_idx,status) values ($bill_idx,1,0)");
            } else if ($status == 'opposition') {
                $result = $this->db->query("insert into UserEvaluationBill(bill_idx,user_idx,status) values ($bill_idx,1,1)");
            }
        }
        $result_array = array();
        $result_array['response_code'] = (boolean)$result;
        return json_encode($result_array);
    }

    public function commentEvaluationWrite($comment_check, $comment_idx, $status)
    {
        $result = $this->db->query("select count(*) as 'count' from CommentRating where $comment_check=$comment_idx")->row();
        if ($result->count >= 1) {
            if ($status == 'agreement') {
                $result = $this->db->query("update CommentRating set status=0 where user_idx=1 and $comment_check=$comment_idx");
            } else if ($status == 'opposition') {
                $result = $this->db->query("update CommentRating set status=1 where user_idx=1 and $comment_check=$comment_idx");
            } else {
                $result = $this->db->query("delete from CommentRating where user_idx=1 and $comment_check=$comment_idx");

            }
        } //처음 클릭 하는경우
        else {
            if ($status == 'agreement') {
                $result = $this->db->query("insert into CommentRating($comment_check,user_idx,status) values ($comment_idx,1,0)");
            } else if ($status == 'opposition') {
                $result = $this->db->query("insert into CommentRating($comment_check,user_idx,status) values ($comment_idx,1,1)");
            }
        }

            $result_array = array();
            $result_array['response_code'] = (boolean)$result;
            return json_encode($result_array);

    }
}