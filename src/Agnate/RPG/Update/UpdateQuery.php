<?php

use \Agnate\RPG\App;

namespace Agnate\RPG\Update;

class UpdateQuery {

  public $statement;
  public $data;

  /**
   * Create a basic query.
   * @param $statement String database statement to run. Values should be represented by tokens.
   * @param $data Array of token => value pairs that will be passed to PDO to replace tokens with values.
   */
  function __construct($statement, Array $data) {
    $this->statement = $statement;
    $this->data = $data;
  }

  function __toString() {
    return str_replace(array_keys($this->data), array_values($this->data), $this->statement);
  }

  /**
   * Run the query.
   */
  public function execute () {
    $query = \Agnate\RPG\App::query($this->statement);
    return $query->execute($this->data);
  }
}