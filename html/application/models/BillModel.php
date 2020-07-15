<?php
/*
 * 법안 모아보기 법안 상세보기 등
 * 법안에 관련된 DB를 참조 하는데 사용하는 모델
 * */

class BillModel extends CI_Model
{
    public $dummy = 20;

    function __construct()//생성자, 제일 먼저 실행되는 일종의 초기화
    {
        parent::__construct();
    }

    function getBillCard($index, $token_data, $generation, $status)
    {
        $time_start = microtime(true);

        $sql_add = '';
        $status_sql = '';
        $generation_sql = '';
        $injection_param = array();

        //값이 null 이 아니면 배열로 만든다
        if ($generation != null) {
            $generation = explode(",", $generation);
        }
        if ($status != null) {
            $status = explode(",", $status);
        }

        //둘중 한개라도 null 이 아니면 where 절을 만들어준다
        if ($generation != null or $status != null) {
            $sql_add = 'where ';
        }


        //pram의 갯수만큼 sql문을 동적으로 만들어줌
        if ($generation != null) {
            foreach ($generation as $key => $value) {
                array_push($injection_param, (int)$value);
                if ($key + 1 == count($generation)) {
                    $generation_sql = $generation_sql . " generation=?";
                } else {
                    $generation_sql = $generation_sql . " generation=? or";
                }
            }
            $generation_sql = "(" . $generation_sql . ")";
        }
        if ($status != null) {
            foreach ($status as $key => $value) {
                array_push($injection_param, $value);
                if ($key + 1 == count($status)) {
                    $status_sql = $status_sql . " progress_status=?";
                } else {
                    $status_sql = $status_sql . " progress_status=? or";
                }
            }
            $status_sql = "(" . $status_sql . ")";
        }


//        echo "1".microtime(true)-$time_start."\n";
        $sql_add = $sql_add . $generation_sql . $status_sql;
        $sql_add = str_replace(")(", ") and (", $sql_add);
        $index = ($index - 1) * 10;
//        echo $sql_add;
//        return;
        array_push($injection_param, $index);
        array_push($injection_param, 10);
        $result = array();
//        echo "2".microtime(true)-$time_start."\n";

        $sql = "select  idx,bill_number,bill_type,title,proposer,progress_status,proposal_date from Bill " . $sql_add . " order by bill_number desc limit ?, ?";
//        echo $sql;
//        var_dump( $injection_param);
//        return;
        //법안 모아보기 정보
        $bill_info = $this->db->query($sql, $injection_param)->result();
        unset($injection_param[count($injection_param) - 1]);
        unset($injection_param[count($injection_param) - 1]);
        //페이징을 위한 총 페이지수
        $sql = "select count(idx) as total from Bill " . $sql_add;
        $bill_total_rows = $this->db->query($sql, $injection_param)->row();
        $result['total_count'] = (int)$bill_total_rows->total;
        $result['total_page'] = ceil($bill_total_rows->total / 10);
        $bill_array = array();
//        echo "3".microtime(true)-$time_start."\n";

        foreach ($bill_info as $row) {

            $bill_data = array();
            $bill_data['bill_idx'] = (int)$row->idx;
            $bill_data['bill_number'] = (int)$row->bill_number;
            $bill_data['bill_type'] = $row->bill_type;
            $bill_data['title'] = $row->title;
            $bill_data['proposer'] = $row->proposer;
            $bill_data['progress_status'] = $row->progress_status;
            $bill_data['proposal_date'] = (int)$row->proposal_date;
//            echo "3-1".microtime(true)-$time_start."\n";
            if ($token_data->idx == "토큰실패") {
                $bill_data['bookmark'] = false;
            } else {
                $bill_data['bookmark'] = $this->getBookmark($token_data->idx, $row->idx);
            }
            //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
            $bill_data['proposer_list'] = $this->billIndexToPoliticians($row->idx);
//            echo "3-2".microtime(true)-$time_start."\n";


            array_push($bill_array, $bill_data);

        }
//        echo "4".microtime(true)-$time_start;
        $result['info'] = $bill_array;
        return json_encode($result);
    }

    //법안 상세보기에 들어갈 데이터
    function billInfo($index, $token_data)
    {
        $result = array();
        $data = array();
        $sql = "select * from Bill where idx=?";
        $bill_rows = $this->db->query($sql, array($index))->row();
        $data['idx'] = (int)$bill_rows->idx;
        $data['bill_number'] = $bill_rows->bill_number;
        $data['bill_type'] = $bill_rows->bill_type;
        $data['title'] = $bill_rows->title;
        $data['proposer'] = $bill_rows->proposer;
        $data['progress_process'] = $bill_rows->progress_process;
        $data['progress_status'] = $bill_rows->progress_status;
        $data['content'] = $bill_rows->content;
        $data['proposal_date'] = (int)$bill_rows->proposal_date;
        $data['proposal_session'] = $bill_rows->proposal_session;
        $data['vote_date'] = (int)$bill_rows->vote_date;
        if ($token_data->idx == '토큰실패') {
            $data['bookmark'] = false;
        } else {
            $data['bookmark'] = $this->getBookmark($token_data->idx, $bill_rows->idx);

        }
//        법안 idx 로 법안을 발의한 사람들의 정보가 들어감
        $data['proposer_list'] = $this->billIndexToPoliticians($index);
//        법안 idx로 법안 투표에 참여한 사람들의 표결 결과 , 정치인 정보 반환
        $data['vote_list'] = $this->billIndexToBillVote($index);


        $result['bill_info'] = $data;


        return json_encode($result);

    }

    //법안인덱스로 -> 법안을 발의한 정치인들의 정보를 반한다  //정치인 idx , 정치인 이름 , 대표발의여부 , 정당인덱스, 정당이름
    private function billIndexToPoliticians($bill_idx)
    {
        $proposer_array = array();
        $sql = "select * from BillProposer  where bill_idx =?";
        $rows = $this->db->query($sql, array($bill_idx))->result();
        foreach ($rows as $row) {
            $array = array();
            $politician_idx = 0;
            $type = '';
            if ($row->representative_idx != null) {
                $politician_idx = $row->representative_idx;
                $type = '대표';
            } else if ($row->together_idx != null) {
                $politician_idx = $row->together_idx;
                $type = '공동';
            } else {
                $politician_idx = $row->agreement_idx;
                $type = '찬성';
            }
            $sql = "select * from Politician where idx=?";
            $politician = $this->db->query($sql, array($politician_idx))->row();
            $array['idx'] = (int)$politician_idx;
            $array['typo'] = $type;
            $array['kr_name'] = $politician->kr_name;
            $array['party_idx'] = (int)$row->party_idx;
            $sql = "select * from Party where idx =?";
            $party = $this->db->query($sql, array($row->party_idx))->row();
            $array['party_name'] = $party->name;
            array_push($proposer_array, $array);
        }
        return $proposer_array;


    }

    //법안인덱스로 해당 법안에 참여한 인원은 몇명이고 찬성,반대,기권,불참한 정치인들의 idx를 가져옴
    private function billIndexToBillVote($index)
    {
        $result = array();
//        $total_array=array();
        $sql = "select politician_idx,party_idx,vote_status from BillVote where bill_idx=?";
        $vote_rows = $this->db->query($sql, array($index))->result();
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

            $array = array();
            $politician_idx = (int)$row->politician_idx;
            $sql = "select * from Politician where idx=?";
            $politician_kr_name = $this->db->query($sql, array($politician_idx))->row()->kr_name;
            $party_idx = (int)$row->party_idx;
            $sql = "select * from Party where idx =?";
            $party_name = $this->db->query($sql, array($party_idx))->row()->name;
            $array['idx'] = $party_idx;
            $array['kr_name'] = $politician_kr_name;
            $array['party_idx'] = $party_idx;
            $array['party_name'] = $party_name;
            if ($row->vote_status == 1) {
                array_push($agreement_array, $array);
            } else if ($row->vote_status == 2) {
                array_push($opposition_array, $array);
            } else if ($row->vote_status == 3) {
                array_push($abstention_array, $array);
            } else {
                array_push($absence_array, $array);
            }


        }
        $result['agreement'] = $agreement_array;
        $result['opposition'] = $opposition_array;
        $result['abstention'] = $abstention_array;
        $result['absence'] = $absence_array;

        return $result;


    }

    //법안 상세보기에 들어가면 댓글을 볼수있음
    //찬성에 쓴 댓글 , 반대에 쓴 댓글
    //해당 댓글에 달린 답글갯수
    public function billCommentList($index, $comment_page, $status, $token_data)
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
            if ($token_data->idx == "토큰실패") {
                $data['like_status'] = "좋아요, 싫어요 안함";

            } else {

                $like_status = $this->db->query("select status, count(idx) as count from CommentRating where user_idx = $token_data->idx and comment_idx=$row->idx")->row();
                if ($like_status->count == 0) {
                    $data['like_status'] = "좋아요, 싫어요 안함";
                } else {
                    if ($like_status->status == 1)
                        $data['like_status'] = '좋아요';
                    else
                        $data['like_status'] = '싫어요';
                }
            }
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
    public function billSubCommentList($index, $sub_comment_page, $token_data)
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
            if ($token_data->idx == "토큰실패") {
                $data['like_status'] = "좋아요, 싫어요 안함";
            } else {

                $like_status = $this->db->query("select status, count(idx) as count from CommentRating where user_idx = $token_data->idx and sub_comment_idx=$row->idx")->row();
                if ($like_status->count == 0) {
                    $data['like_status'] = "좋아요, 싫어요 안함";
                } else {
                    if ($like_status->status == 1)
                        $data['like_status'] = '좋아요';
                    else
                        $data['like_status'] = '싫어요';
                }
            }

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
    public function billCommentWrite($bill_idx, $content, $status, $token_data)
    {

        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }
        if ($status == 'agreement') {
            $this->db->query("insert into Comment(user_idx,bill_idx,content,create_at,status) values ($token_data->idx,$bill_idx,'$content',NOW(),0)");

        } else if ($status == 'opposition') {
            $this->db->query("insert into Comment(user_idx,bill_idx,content,create_at,status) values ($token_data->idx,$bill_idx,'$content',NOW(),1)");

        }
        $result_json = array();
        $comment_idx = $this->db->insert_id();
        $result_json['comment_idx'] = (int)$comment_idx;


        return json_encode($result_json);
    }
    //사용자가 댓글에 대한 답글을 작성 할 경우 (부보 인덱스가 없는경우)
    // or
    // 사용자가 답글에 대한 답글을 작성 할 경우 (부모 인덱스가 있는경우)
    public function billSubCommmentWrite($comment_idx, $content, $parent_idx, $token_data)
    {
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        if ($parent_idx == null) {
            $this->db->query("insert into SubComment(comment_idx,user_idx,content,create_at) values ($comment_idx,$token_data->idx,'$content',NOW())");
        } else {
            $this->db->query("insert into SubComment(comment_idx,user_idx,content,parent_user_idx,create_at) values ($comment_idx,$token_data->idx,'$content',$parent_idx,NOW())");
        }
        $result = array();
        $result['sub_comment_idx'] = $this->db->insert_id();
        return json_encode($result);
    }

    //사용자가 법안에 대해 좋아요 혹은 싫어요를 클릭함
    //사용자가 해당 법안에 대해 처음 좋아요 , 싫어요 클릭했을시에 row 생성
    //사용자가 좋아요 - > 싫어요 또는 싫어요 - > 좋아요 를 클릭했을시에 row 업데이트
    //사용자가 좋아요 해제 , 싫어요 해제  클릭시 row 삭제
    public function billEvaluationWrite($bill_idx, $status, $token_data)
    {
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        $result = $this->db->query("select count(*) as 'count' from UserEvaluationBill where bill_idx=$bill_idx")->row();

//        return $result->count;
//        return $status;

        //이미 데이터가 있는 경우
        if ($result->count >= 1) {
            if ($status == 'agreement') {
                $result = $this->db->query("update UserEvaluationBill set status=0 where user_idx=$token_data->idx and bill_idx=$bill_idx");
            } else if ($status == 'opposition') {
                $result = $this->db->query("update UserEvaluationBill set status=1 where user_idx=$token_data->idx and bill_idx=$bill_idx");
            } else {
                $result = $this->db->query("delete from UserEvaluationBill where user_idx=$token_data->idx and bill_idx=$bill_idx");
            }
        } //처음 클릭 하는경우
        else {
            if ($status == 'agreement') {
                $result = $this->db->query("insert into UserEvaluationBill(bill_idx,user_idx,status) values ($bill_idx,$token_data->idx,0)");
            } else if ($status == 'opposition') {
                $result = $this->db->query("insert into UserEvaluationBill(bill_idx,user_idx,status) values ($bill_idx,$token_data->idx,1)");
            }
        }
        $result_array = array();
        $result_array['response_code'] = (boolean)$result;
        return json_encode($result_array);
    }

    public function commentEvaluationWrite($comment_check, $comment_idx, $status, $token_data)
    {
        if ($token_data->idx == "토큰실패") {
            $result_json = array();
            $result_json['result'] = '로그인 필요';
            header("HTTP/1.1 401 ");
            return json_encode($result_json);
        }

        $result = $this->db->query("select count(*) as 'count' from CommentRating where $comment_check=$comment_idx")->row();
        if ($result->count >= 1) {
            if ($status == 'agreement') {
                $result = $this->db->query("update CommentRating set status=0 where user_idx=$token_data->idx and $comment_check=$comment_idx");
            } else if ($status == 'opposition') {
                $result = $this->db->query("update CommentRating set status=1 where user_idx=$token_data->idx and $comment_check=$comment_idx");
            } else {
                $result = $this->db->query("delete from CommentRating where user_idx=$token_data->idx and $comment_check=$comment_idx");

            }
        } //처음 클릭 하는경우
        else {
            if ($status == 'agreement') {
                $result = $this->db->query("insert into CommentRating($comment_check,user_idx,status) values ($comment_idx,$token_data->idx,0)");
            } else if ($status == 'opposition') {
                $result = $this->db->query("insert into CommentRating($comment_check,user_idx,status) values ($comment_idx,$token_data->idx,1)");
            }
        }

        $result_array = array();
        $result_array['response_code'] = (boolean)$result;
        return json_encode($result_array);

    }

    public function getBookmark($user_idx, $bill_idx)
    {
        $sql = "select count(idx) as count from BookMark where user_idx=? and bill_idx=?";
        $bill_bookmark = $this->db->query($sql, array((int)$user_idx, (int)$bill_idx))->row();
        $check = (int)$bill_bookmark->count;
        if ($check == 1) {
            return true;
        } else {
            return false;
        }

    }

    public function getBillEvaluation($token_data, $bill_idx)
    {


        $result = array();
        if ($token_data->idx == '토큰실패') {
            $result['evaluation'] = null;
        } else {
            $sql = "select count(idx) as count from UserEvaluationBill where user_idx=? and bill_idx=?";
            $count = (int)$this->db->query($sql, array((int)$token_data->idx, (int)$bill_idx))->row()->count;
            if ($count == 0) {
                $result['evaluation'] = null;
            } else {
                $sql = "select status  from UserEvaluationBill where user_idx=? and bill_idx=?";
                $status = (int)$this->db->query($sql, array((int)$token_data->idx, (int)$bill_idx))->row()->status;
                if ($status == 0) {
                    $result['evaluation'] = '싫어요';
                } else if ($status == 1) {
                    $result['evaluation'] = '좋아요';
                }
            }
        }
        $sql = "select count(idx) as count  from UserEvaluationBill where bill_idx=? and status=?";
        $result['opposition_count'] = (int)$this->db->query($sql, array( (int)$bill_idx, 0))->row()->count;
        $result['agreement_count'] = (int)$this->db->query($sql, array( (int)$bill_idx, 1))->row()->count;
        return json_encode($result);

    }

    public function putBookmark($bill_idx, $user_idx)
    {
        $sql = "select count(idx) as count from BookMark where user_idx =? and bill_idx=?";
        $row = (int)$this->db->query($sql, array((int)$user_idx, (int)$bill_idx))->row()->count;
        $result = array();
        if ($row == 0) {
            $sql = "insert into BookMark(user_idx,bill_idx,create_at) values (?,?,NOW())";
            $this->db->query($sql, array($user_idx, $bill_idx));
            $result['result'] = '북마크 추가';
        } else {
            $sql = "delete from BookMark where  user_idx=? and bill_idx=?";
            $this->db->query($sql, array($user_idx, $bill_idx));
            $result['result'] = '북마크 삭제';
        }
        return json_encode($result);

    }

    public function putBillEvaluation($bill_idx, $user_idx, $status)
    {
        if ($status=='dislike'){
            $status=0;
        }else if ($status =='like'){
            $status=1;
        }
        $sql = "select count(idx) as count from UserEvaluationBill where user_idx=? and bill_idx=?";
        $count =(int)$this->db->query($sql,array((int)$user_idx,(int)$bill_idx))->row()->count;
        $result=array();
        //아무런 체크를 하지 않았으면 insert
        if ($count==0){
            $sql="insert into UserEvaluationBill(bill_idx,user_idx,status) values(?,?,?)";
            $this->db->query($sql, array((int)$bill_idx, (int)$user_idx,(int)$status));
            $result['result']=(int)$status;
        }else{
        //체크가 되어있다면
            $sql="select status from UserEvaluationBill where user_idx=? and bill_idx=?";
            $user_status=(int)$this->db->query($sql,array($user_idx,$bill_idx))->row()->status;
            if ($user_status==(int)$status){
                $sql="delete from UserEvaluationBill where user_idx=? and bill_idx=? and status=?";
                $this->db->query($sql,array($user_idx,$bill_idx,(int)$status));
                $result['result']=null;
            }else{
                $sql="update UserEvaluationBill set status=? where user_idx=? and bill_idx=? ";
                $this->db->query($sql,array((int)$status,$user_idx,$bill_idx));
                $result['result']=(int)$status;
            }
        }
        return json_encode($result);
    }

    // 법안 과정(status)에 대한 데이터를 가져와야한다.
    public function getBillProcessDetail($bill_idx, $status){

        $sql = "select content from BillDetailInfo where bill_idx = ? and progress_status = ?";
        $bill_info = $this->db->query($sql, array((int)$bill_idx, $status))->row();

        // 법안 정보가 없을때, 요청은 성공했지만 반환데이터가 없다는 204응답을 준다.
        if($bill_info == null){
            header("HTTP/1.1 204 ");
            return;
        }
        else{
            return $bill_info->content;
        }
    }
}