<?php

namespace Agnate\RPG;

class Trigger {

  public $commands;
  public $action;
  public $command_args;
  public $args;

  /**
   * @param $commands Array of text strings that will trigger an action.
   * @param $action Action class name that will be triggered by the command(s). Example: 'GreetingAction'.
   * @param $args Any number of additional parameters that act as additional arguments to be passed to the Action.
   */
  function __construct($commands, $action) {
    if (!is_array($commands)) throw new \Exception('Trigger commands must be an Array of Strings.');
    if (!class_exists($action) || !in_array('Agnate\RPG\Action\ActionInterface', class_implements($action))) throw new \Exception('Trigger action must be the name of an Action class and must implement ActionInterface, "' . $action . '" given.');

    // Set the defaults.
    $this->action = $action;
    $this->commands = array();
    // For all commands to be lower-case.
    foreach ($commands as $command) {
      $this->commands[] = strtolower($command);
    }

    // Pull out any additional arguments and hold onto them to pass to the action.
    $args = func_get_args();
    array_splice($args, 0, 2);
    $this->command_args = $args;
  }

  /**
   * Check if this Trigger will run on this command.
   * @param $input String of a command the player typed.
   * @return Returns TRUE if it will be triggered, FALSE otherwise.
   */
  public function is_triggered ($input) {
    foreach ($this->commands as $command) {
      // Allows for partial command checks.
      $input_to_check = substr($input, 0, strlen($command));
      // Check the fully lower-cased command.
      $check = strpos(strtolower($input_to_check), $command);
      if ($check !== FALSE && $check === 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Perform the action stored by this trigger.
   * @param $input Text input from the player to perform the action on.
   * @return Returns a Layout object that can be encoded into an output for Slack or browsers.
   */
  public function perform_action ($input) {
    if (!$this->is_triggered($input)) return FALSE;

    // Explode the arguments based on a space, then shift off the first item (which is the command).
    $args = explode(' ', trim($input));
    array_shift($args);
    $this->args = $args;

    // Must convert this to a variable to trigger PHP's scope resolution operator (http://php.net/manual/en/language.oop5.paamayim-nekudotayim.php).
    $action = $this->action;

    // Trigger the action and pass it the arguments.
    return $action::perform(array_merge($this->args, $this->command_args));
  }

}