<?php
function getLeaderBoard($con, $event_code,$sub_event_code){
    $scores=[];
    if($sub_event_code=="global"){
        $scores=getScoreGlobal($con,$event_code);
    }else{
        $scores=getScores($con,$event_code,$sub_event_code);
    }

}
function getScores($con, $event_code,$sub_event_code){
    $score_res= mysqli_query($con, "SELECT * FROM `scores` WHERE event_code='$event_code' AND sub_event_code='$sub_event_code'");
    $scores = [];
    while ($score = mysqli_fetch_assoc($score_res)) {
        $scores[$score["player_id"]] = $score;
    }
    return $scores;
}
function getScoreGlobal($con, $event_code){
    $sub_event_list=getSubEventsList($con,$event_code);
    $scores=[];
    foreach($sub_event_list as $sub_event){
        $scores_event_list=getScores($con,$event_code,$sub_event["sub_event_code"]);
        foreach($scores_event_list as $player_id=>$score){
            if(!isset($scores[$player_id])){
                $scores[$player_id]=["player_id"=>$player_id,"score"=>0,"player_name"=>$score["player_name"],"sub_scores_list"=>[]];
            }
            $scores[$player_id]["score"]+=$score["score"];
            $scores[$player_id]["sub_scores_list"][$sub_event["sub_event_code"]]=$score["score"];
        }
    }
    return $scores;
}
function getSubEventsList($con,$event_code){
    $sub_events_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE event_code='$event_code'");
    $sub_event_lists = [];
    while ($sub_event = mysqli_fetch_assoc($sub_events_res)) {
        $sub_event_lists[] = $sub_event;
    }
    return $sub_event_lists;
}
function getEventData($con, $event_code){
    $event_datas_res = mysqli_query($con, "SELECT * FROM `events` WHERE event_code='$event_code'");
    if (mysqli_num_rows($event_datas_res) == 0) {
        return false;
    }
    $event_datas = mysqli_fetch_assoc($event_datas_res);
    if(count($event_datas) == 0){
        return false;
    }
    if($event_datas===false){
        return false;
    }
    return $event_datas;
}
function getSubEventData($con,$event_code,$sub_event_code){
    $sub_event_datas_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE event_code='$event_code' AND sub_event_code='$sub_event_code'");
    if (mysqli_num_rows($sub_event_datas_res) == 0) {
        return false;
    }
    $sub_event_datas = mysqli_fetch_assoc($sub_event_datas_res);
    if(count($sub_event_datas) == 0){
        return false;
    }
    if($sub_event_datas===false){
        return false;
    }
    return $sub_event_datas;
}
?>