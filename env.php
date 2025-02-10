<?php
$db_host = "localhost";
$db_usr = "";
$db_pass = "";
$db_db = "makernight_leaderboard";
$db_port = 3306;
if(file_exists("env_override.php")){
    include "env_override.php";
}