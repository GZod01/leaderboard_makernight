# LeaderBoard designed for the UniMakers MakerNight event

## How to use?
Create a mysql database on your server, in this database create tables following schemas in "config.php" or below in README.md:
```sql
CREATE TABLE events (
    event_code VARCHAR(255) PRIMARY KEY,
    event_name VARCHAR(255),
    admin_pass VARCHAR(255)
)
CREATE TABLE sub_events (
    event_code VARCHAR(255),
    sub_event_code VARCHAR(255),
    sub_event_name VARCHAR(255),
    sub_event_type INT(2) COMMENT '0: score, 1: speedrun(time based score)',
    start_time INT(11) DEFAULT 0,
    PRIMARY KEY (event_code, sub_event_code)
)
CREATE TABLE scores (
    event_code VARCHAR(255),
    sub_event_code VARCHAR(255),
    player_id VARCHAR(255),
    score INT(11),
    score_time INT(11),
    PRIMARY KEY (event_code, sub_event_code, player_id)
)
CREATE TABLE event_players (
    event_code VARCHAR(255),
    player_id VARCHAR(255),
    player_name VARCHAR(255),
    PRIMARY KEY (event_code, player_id)
)
```
Then just change the env.php or create an env_override.php. Enjoy the leaderboard!

## Contributing
Contribution is welcome to help the dev with this tool. To contribute, just fork the repo, make your changes and then create a pull requests.
