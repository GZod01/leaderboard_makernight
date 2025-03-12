<?php
require "configDB.php";
$defaultConfig = [
    "title"=>"Hello"
];
$config=[];
if(file_exists("config.json")){
    $confoverride = json_decode(file_get_contents("config.json"),true);
    $config = array_merge($defaultConfig,$confoverride);
}

function siteConf(string $key){
    global $config;
    return $config[$key];
}
