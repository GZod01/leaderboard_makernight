<?php
header('Content-Type: application/json');
if (!isset($_POST["password"], $_POST["uuid"], $_POST["event_code"], $_POST["sub_event_code"])) {
    die(json_encode(["status" => "error", "message" => "Invalid Request"]));
}
require "config.php";
$uuid = $_POST["uuid"];
$event_code = $_POST["event_code"];
$sub_event_code = $_POST["sub_event_code"];
$password = $_POST["password"];
$event_data = getEventData($con, $event_code);
if ($event_data === false) {
    die(json_encode(["status" => "error", "message" => "Event not found"]));
}
$sub_event_data = getSubEventData($con, $event_code, $sub_event_code);
if ($sub_event_data === false) {
    die(json_encode(["status" => "error", "message" => "Sub Event not found"]));
}
// Password is a password sent from the client to register his result, it's mainly used for code events where participants have to find a password with their code and then submit it to the server to register their result
if ($sub_event_data["http_password"] !== $password) {
    die(json_encode(["status" => "failed", "message" => "Invalid Password Try Again"]));
}
$player_res = mysqli_query($con, "SELECT * FROM `event_players` WHERE player_id='$uuid'");
$player = mysqli_fetch_assoc($player_res);
if ($player === false) {
    die(json_encode(["status" => "error", "message" => "Player not found"]));
}
$score = 0;
if ($sub_event_data["sub_event_type"] == 0) {
    $score = 10;
} elseif ($sub_event_data["sub_event_type"] == 1) {
    $score_time_req = time();
    $start_time = $sub_event_datas["start_time"];
    $timelength = $score_time_req - $start_time;
    $get_position_in_classment = mysqli_query($con, "SELECT score_time FROM `scores` WHERE event_code='$event_code' AND sub_event_code='$sub_event_code' AND score_time<$score_time ORDER BY score_time ASC");
    $position_in_classment = mysqli_num_rows($get_position_in_classment) + 1;
    $bestscore = $timelength;
    if ($position_in_classment != 1) {
        $gpic = $get_position_in_classment->fetch_all()[0][0];
        $bestscore = $gpic[0][0];
    }
    $score = (20000 * $bestscore) / ($timelength * $position_in_classment);
    $score = intval($score);
}
$query = ("INSERT INTO `scores` (event_code, sub_event_code, player_id, score, score_time) VALUES ('$event_code', '$sub_event_code', '$player_id', $score, $score_time) ON DUPLICATE KEY UPDATE score=$score, score_time=$score_time");
mysqli_query($con, $query);

die(print_r(json_encode(["status" => "success", "message" => "Score Registered"])));