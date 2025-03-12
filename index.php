<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "config.php";
require "leaderboardtools.php";
if (!isset($_GET["event_code"])) {
    die("No event code = no leaderboard");
}
$event_code = $_GET["event_code"];
$event_code= mysqli_real_escape_string($con,$event_code);
$event_datas = getEventData($con, $event_code);
if ($event_datas === false) {
    die("Event not found");
}
if (isset($_GET["admin"])) {
    if (isset($_POST["pass"])) {
        $sup = "&error";
        if (md5($_POST["pass"]) == $event_datas["admin_pass"]) {
            $_SESSION["admin_login"] = true;
            $sup = "&success";
        }


        die(header("Location: /?event_code=$event_code&admin$sup"));
    }
    if (!isset($_SESSION["admin_login"])) {
?>
        <form action="" method="post">
            <?= isset($_GET["error"]) ? "<p>Mot de passe incorrect</p>" : "" ?>
            <label for=pass>Mot de passe admin:<input type=password name=pass id=pass></label>
            <input type=submit value=Valider>
        </form>
        <?php
    } else {
        $players_res = mysqli_query($con, "SELECT * FROM `event_players` WHERE event_code='$event_code'");
        $players = [];
        while ($player = mysqli_fetch_assoc($players_res)) {
            $players[] = $player;
        }
        $sub_events_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE event_code='$event_code'");
        $sub_events = [];
        while ($sub_event = mysqli_fetch_assoc($sub_events_res)) {
            $sub_events[] = $sub_event;
        }
        if ($_GET["admin"] === "players") {
            if (isset($_GET["add"])) {
                if (isset($_POST["player_name"])) {
                    $player_name = mysqli_real_escape_string($con,$_POST["player_name"]);
                    $player_id = md5(uniqid($player_name));
                    mysqli_query($con, "INSERT INTO `event_players` (event_code, player_id, player_name) VALUES ('$event_code', '$player_id', '$player_name')");
                    die(header("Location: ?event_code=$event_code&admin=players&add"));
                }
                ?>
                <form action="" method="post">
                    <label for=player_name>Nom du joueur:<input type=text name=player_name id=player_name></label>
                    <input type=submit value=Valider>
                </form>
                <?php
            } else if (isset($_GET["remove"])) {
                if (isset($_POST["player_id"])) {
                    $player_id = $_POST["player_id"];
                    mysqli_query($con, "DELETE FROM `event_players` WHERE event_code='$event_code' AND player_id='$player_id'");
                }
                foreach ($players as $player) {
                ?>
                    <form action="" method="post">
                        <input type=hidden name=player_id value="<?= $player["player_id"] ?>">
                        <input type=submit value="Supprimer <?= htmlspecialchars($player["player_name"]) ?>">
                    </form>
            <?php
                }
            }
            ?>
            <h1>Admin de l'event <?= $event_datas["event_name"] ?></h1>
            <h2><a href="/?event_code=<?= $event_code ?>&admin=players&add">Ajouter un joueur</a></h2>
            <h2><a href="/?event_code=<?= $event_code ?>&admin=players&remove">Supprimer un joueur</a></h2>
            <p>Liste des joueurs:</p>
            <ul>
                <?php
                foreach ($players as $player) {
                ?>
                    <li><?= htmlspecialchars($player["player_name"]) ?></li>
                <?php
                }
                ?>
            </ul>
        <?php
        }else if ($_GET["admin"]=="subevents"){
            if(isset($_GET["add"])){
                if(isset($_POST["sub_event_name"])){
                    $sub_event_name = mysqli_real_escape_string($con,$_POST["sub_event_name"]);
                    $sub_event_code = md5(uniqid($sub_event_name));
                    $sub_event_type = 0;
                    if(isset($_POST["sub_event_type"])){
                        $sub_event_type = intval($_POST["sub_event_type"]);
                    }
                    mysqli_query($con, "INSERT INTO `sub_events` (event_code, sub_event_code, sub_event_name, sub_event_type) VALUES ('$event_code', '$sub_event_code', '$sub_event_name', $sub_event_type)");
                }
                ?>
                <form action="" method="post">
                    <label for=sub_event_name>Nom du sous event:<input type=text name=sub_event_name id=sub_event_name></label>
                    <label for=sub_event_type>Type de sous event:<select name=sub_event_type id=sub_event_type>
                        <option value=0>Score</option>
                        <option value=1>Speedrun</option>
                    </select></label>
                    <label for=sub_event_multiplier>Global score multiplier:<input type=number name=sub_event_multiplier id=sub_event_multiplier></label>
                    <label for=sub_event_http_password>Event HTTP Request password:<input type=text name=sub_event_http_password id=sub_event_http_password></label>
                    <input type=submit value=Valider>
                </form>
                <?php
            }
            else if(isset($_GET["remove"])){
                if(isset($_POST["sub_event_code"])){
                    $sub_event_code = $_POST["sub_event_code"];
                    mysqli_query($con, "DELETE FROM `sub_events` WHERE event_code='$event_code' AND sub_event_code='$sub_event_code'");
                }
                foreach($sub_events as $sub_event){
                ?>
                    <form action="" method="post">
                        <input type=hidden name=sub_event_code value="<?= $sub_event["sub_event_code"] ?>">
                        <input type=submit value="Supprimer <?= htmlspecialchars($sub_event["sub_event_name"]) ?>">
                    </form>
                <?php
                }
            }else if (isset($_GET["start"])) {
                if (isset($_POST["sub_event_code"])) {
                    $sub_event_code = $_POST["sub_event_code"];
                    $check_if_event_started = getSubEventData($con, $event_code,$sub_event_code);
                    if($check_if_event_started["start_time"]!=0){
                        die("<p>Sub event already started <a href='/?event_code=$event_code&admin=subevents&start'>return</a></p>");
                    }
                    mysqli_query($con, "UPDATE `sub_events` SET start_time=".time()." WHERE event_code='$event_code' AND sub_event_code='$sub_event_code'");
                }
                foreach ($sub_events as $sub_event) {
                ?>
                    <form action="" method="post">
                        <input type=hidden name=sub_event_code value="<?= $sub_event["sub_event_code"] ?>">
                        <input type=submit value="Démarrer <?= htmlspecialchars($sub_event["sub_event_name"]) ?>">
                    </form>
                <?php
                }
            }
            ?>
            <h1>Admin de l'event <?= $event_datas["event_name"] ?></h1>
            <h2><a href="/?event_code=<?= $event_code ?>&admin=subevents&add">Ajouter un sous event</a></h2>
            <h2><a href="/?event_code=<?= $event_code ?>&admin=subevents&remove">Supprimer un sous event</a></h2>
            <h2><a href="/?event_code=<?= $event_code ?>&admin=subevents&start">Démarrer un sous event</a></h2>
            <p>Liste des sous events:</p>
            <ul>
                <?php
                foreach ($sub_events as $sub_event) {
                ?>
                    <li><a href="/?event_code=<?= $event_code ?>&sub_event_code=<?= $sub_event["sub_event_code"] ?>"><?= $sub_event["sub_event_name"] ?></a></li>
                <?php
                }
                ?>
            <?php
        }
        else if($_GET["admin"]==="score"){
            if(isset($_GET["sub_event_code"])){
                $sub_event_code = $_GET["sub_event_code"];
                $sub_event_datas = getSubEventData($con, $event_code, $sub_event_code);
                if($sub_event_datas===false){
                    die("Sub event not found");
                }
                $is_speedrun = $sub_event_datas["sub_event_type"]==1;
                if(isset($_POST["player_id"]) and ($is_speedrun?isset($_POST["score_time"]):isset($_POST["score"]))){
                    if($sub_event_datas["start_time"]==0){
                        die("WARNING ! sub event not started");
                    }
                    $player_id = $_POST["player_id"];
                    $start_time = $sub_event_datas["start_time"];
                    $timelength = strtotime($_POST["score_time"]??(date("D, d M Y H:i:s",$start_time+1)))-$start_time;
                    $score_time = $timelength;
                    if($timelength<=0){
                        die("WARNING ! score time is before start time <a href='?event_code=$event_code&admin=score&sub_event_code=$sub_event_code'>refresh</a>");
                    }
                    $score = 0;
                    if($is_speedrun){
                        //TODO: check this if code block when implementing "http request speedrun" (for dev speedrun or other things that can be complete with a special http request)
                        // SPEEDRUN SCORE CALCULATION
                        $get_position_in_classment = mysqli_query($con, "SELECT score_time FROM `scores` WHERE event_code='$event_code' AND sub_event_code='$sub_event_code' AND score_time<$score_time ORDER BY score_time ASC");
                        $position_in_classment = mysqli_num_rows($get_position_in_classment)+1;
                        $bestscore=$timelength;
                        if($position_in_classment!=1){
                            $gpic=$get_position_in_classment->fetch_all()[0][0];
                            // print_r($gpic);
                            $bestscore = $gpic[0][0];
                        }
                        $score = 10-($timelength/$bestscore);
                        $score = intval($score);
                    }else{
                        $score = intval($_POST["score"]);
                    }
                    
                    $query = ("INSERT INTO `scores` (event_code, sub_event_code, player_id, score, score_time) VALUES ('$event_code', '$sub_event_code', '$player_id', $score, $score_time) ON DUPLICATE KEY UPDATE score=$score, score_time=$score_time");
                    // die($query.print_r(["post"=>$_POST,"timelength"=>$timelength],true));
                    mysqli_query($con, $query);
                }
                ?>
                <h1>Admin de l'event <?= $event_datas["event_name"] ?></h1>
                <h2>Sub event: <?= $sub_event_datas["sub_event_name"] ?></h2>
                <form action="" method="post">
                    <label for=player_id>Joueur:<select name=player_id id=player_id>
                        <?php
                        foreach($players as $player){
                        ?>
                            <option value="<?= $player["player_id"] ?>"><?= htmlspecialchars($player["player_name"]) ?></option>
                        <?php
                        }
                        ?>
                    </select></label>
                    <?php
                    if($is_speedrun){
                    ?>
                        <!-- <label for=score_time>Temps:<input type=time step="1" name=score_time id=score_time></label> -->
                        <p>Le temps sera défini sur le moment de l'envoi de ce formulaire</p>
                    <?php
                    }else{
                    ?>
                        <label for=score>Score:<input type=number name=score id=score></label>
                    <?php
                    }
                    ?>
                    <input type=submit value=Valider>
                </form>
                <?php
            }
            ?>
            <h1>Admin de l'event <?= $event_datas["event_name"] ?></h1>
            <p>Liste des sous events:</p>
            <ul>
                <?php
                foreach($sub_events as $sub_event){
                    ?>
                    <li><a href="/?event_code=<?=$event_code?>&admin=score&sub_event_code=<?=$sub_event["sub_event_code"]?>"><?= $sub_event["sub_event_name"] ?></a></li>
                    <?php
                }
                ?>
            </ul>
            <?php
        }
        ?>
        <a href="/?event_code=<?= $event_code ?>">Retour</a>
        <h1>Admin de l'event <?= $event_datas["event_name"] ?></h1>
        <h2><a href="/?event_code=<?= $event_code ?>&admin=players">Gestion des joueurs</a></h2>
        <h2><a href="/?event_code=<?= $event_code ?>&admin=subevents">Gestion des sous events</a></h2>
        <h2><a href="/?event_code=<?= $event_code ?>&admin=score">Gestion des scores</a></h2>
    <?php
    }
}
$sub_event_code = "global";
if (isset($_GET["sub_event_code"])) {
    $sub_event_code = $_GET["sub_event_code"];
    $escaped_sub_evt_code=mysqli_real_escape_string($con, $sub_event_code);
    $sub_event_data_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE sub_event_code='$escaped_sub_evt_code'");
    if(mysqli_num_rows($sub_event_data_res)<1){
        die("<p><b>Ce sous-event n'existe pas</b> <a href='/?event_code=$event_code'>retourner à l'accueil de l'event.</a></p>");
    }
    $sub_event_data=mysqli_fetch_assoc($sub_event_data_res);
    ?>
    <h1><?= $event_datas["event_name"] ?></h1>
    <h2>Sous event: <?=$sub_event_data["sub_event_name"]?></h2>
    <?=getBuildedLeaderBoard($con, $event_code, $sub_event_code);?>
    <p>Liste des sous events:</p>
    <ul>
        <li><a href="/?event_code=<?=$event_code?>">Global</a></li>
        <?php
        $sub_events_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE event_code='$event_code'");
        $sub_event_lists = [];
        while ($sub_event = mysqli_fetch_assoc($sub_events_res)) {
            $sub_event_lists[] = $sub_event;
        ?>
            <li><a href="/?event_code=<?= $event_code ?>&sub_event_code=<?= $sub_event["sub_event_code"] ?>"><?= $sub_event["sub_event_name"] ?></a></li>
        <?php
        }
        ?>
    </ul>
    <?php
}
if ($sub_event_code == "global") {
    ?>
    <h1><?= $event_datas["event_name"] ?></h1>
    <?= getBuildedLeaderBoard($con, $event_code, "global"); ?>
    <p>Liste des sous events:</p>
    <ul>
        <?php
        $sub_events_res = mysqli_query($con, "SELECT * FROM `sub_events` WHERE event_code='$event_code'");
        $sub_event_lists = [];
        while ($sub_event = mysqli_fetch_assoc($sub_events_res)) {
            $sub_event_lists[] = $sub_event;
        ?>
            <li><a href="/?event_code=<?= $event_code ?>&sub_event_code=<?= $sub_event["sub_event_code"] ?>"><?= $sub_event["sub_event_name"] ?></a></li>
        <?php
        }
        ?>
    </ul>
<?php
    die();
}
$sub_event_datas = getSubEventData($con, $event_code, $sub_event_code);
if ($sub_event_datas === false) {
    die("Sub event not found");
}
?>
