<?php

use \PDO;
use \Agnate\RPG\App;
use \Agnate\RPG\Entity;

namespace Agnate\RPG;

class Team extends Entity {

  public $tid;
  public $team_id; // Slack team ID
  public $team_name; // Slack team name
  public $bot_user_id;
  public $bot_access_token;

  // Static vars
  static $db_table = 'teams';
  static $default_class = '\Agnate\RPG\Team';
  static $partials = array('team_name');
  static $primary_key = 'tid';
  static $relationships = array();
  
}