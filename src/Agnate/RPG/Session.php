<?php
use \Kint;
use Agnate\RPG\App;
use Agnate\RPG\Trigger;
use Agnate\RPG\Action\GreetingAction;

namespace Agnate\RPG;

class Session {

  public $data;
  public $debug = FALSE;
  public $slack_request_data;
  public $response;
  public $triggers = array();

  /**
   * Create a new Session.
   */
  function __construct() {
    // Start the App if it hasn't started already.
    App::start();

    // Register all the triggers.
    $this->triggers[] = new Trigger (array('hello'), '\Agnate\RPG\Action\GreetingAction');
  }

  /**
   * @return Array of Message instances.
   */
  public function run ($input, $slack_request_data = array()) {
    /* Expecting:
    $slack_request_data = array(
      'slack_user_id' => $slack_user_id,
      'slack_team_id' => $slack_team_id,
      'input' => $text,
    )
    */
    $this->data = $slack_request_data;
    $this->debug = (isset($this->data['debug']) && $this->data['debug'] == 'true');

    d($input);
    d($slack_request_data);

    /*
    'type' => 'message',
    'channel' => 'D286C33AR',
    'user' => 'U0265JBJW',
    'text' => 'hello',
    'ts' => '1473045021.000013',
    'team' => 'T025KTDB7',
    */

    foreach ($this->triggers as $trigger) {
      // If the input triggers a command, run the action associated with the trigger.
      if ($trigger->is_triggered($input)) {
        $this->response = $trigger->perform_action($input);
        break;
      }
    }

    // d($this->response);

    // If there's no response, return the "no command found" response.
    if (empty($this->response)) {
      // TODO: Needs to return a nice Help text and in the form of a Message class.
      return 'NO COMMAND';
    }

    // Convert to array to simplify output handling.
    if (!is_array($this->response)) {
      $this->response = array($this->response);
    }

    return $this->response;
  }

}