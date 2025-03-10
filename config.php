<?php
require "env.php";
$_db_events_format = <<<SQL
CREATE TABLE events (
    event_code VARCHAR(255) PRIMARY KEY,
    event_name VARCHAR(255),
    admin_pass VARCHAR(255)
)
SQL;
$_db_sub_events_format = <<<SQL
CREATE TABLE sub_events (
    event_code VARCHAR(255),
    sub_event_code VARCHAR(255),
    sub_event_name VARCHAR(255),
    sub_event_type INT(2) COMMENT '0: score, 1: speedrun(time based score)',
    start_time INT(11) DEFAULT 0,
    multiplier INT(11) DEFAULT 1,
    http_password VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (event_code, sub_event_code)
)
SQL;
$_db_scores_format = <<<SQL
CREATE TABLE scores (
    event_code VARCHAR(255),
    sub_event_code VARCHAR(255),
    player_id VARCHAR(255),
    score INT(11),
    score_time INT(11),
    PRIMARY KEY (event_code, sub_event_code, player_id)
)
SQL;
$_db_event_players_format = <<<SQL
CREATE TABLE event_players (
    event_code VARCHAR(255),
    player_id VARCHAR(255),
    player_name VARCHAR(255),
    PRIMARY KEY (event_code, player_id)
)
SQL;
$con = mysqli_connect($db_host, $db_usr, $db_pass, $db_db, $db_port);