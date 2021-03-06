<?php

use Agnate\RPG\App;
use Agnate\RPG\EntityBasic;
use Agnate\RPG\Message\Attachment;
use Agnate\RPG\Message\Channel;

namespace Agnate\RPG;

class Message extends EntityBasic {

  public $channel; // Channel the Message should render to.
  public $slack_channel;
  public $as_user = TRUE;
  public $text;
  public $attachments;

  // For messages with buttons:
  public $response_type = 'in_channel';
  // public $replace_original; // boolean - ONLY used when using buttons
  // public $delete_original; // boolean - ONLY used when using buttons

  // Set any field keys that are expecting arrays.
  static $fields_array = array('attachments');
  
  /**
   * Create a new Message instance to populate with data so it can be rendered.
   * @param $data Array of keyed values that are dynamically saved to the Entity if declared in the class. Keys are:
   *  'channel' — The Channel for the Message.
   *  'guilds' — The Guild(s) this Message should go to.
   */
  function __construct($data = array()) {
    // Extra validation.
    if (!empty($data['channel']) && !($data['channel'] instanceof Message\Channel)) throw new \Exception ('Message channel must be a Channel object, ' . $data['channel'] . ' given.');
    
    // Convert single Guild into an array.
    if (!empty($data['guilds']) && !is_array($data['guilds'])) {
      $this->guilds = array($guilds);
    }

    // Assign data to instance properties.
    parent::__construct($data);
  }

  /**
   * Add an attachment to the Message.
   */
  public function addAttachment (Message\Attachment $attachment) {
    $this->attachments[] = $attachment;
  }

  /**
   * Provided by JsonSerializable interface.
   */
  public function jsonSerialize() {
    // Gets all of the public variables as a keyed array.
    $payload = call_user_func('get_object_vars', $this);
    // Remove the variables we don't want to serialize to Slack.
    unset($payload['channel']);
    unset($payload['slack_channel']);
    if (!empty($this->slack_channel)) $payload['channel'] = $this->slack_channel;

    // Convert attachments.
    unset($payload['attachments']);
    // if (!empty($this->attachments)) {
    //   $payload['attachments'] = array();
    //   foreach ($this->attachments as $attachment) {
    //     $payload['attachments'][] = $attachment->jsonSerialize();
    //   }
    // }

    // Clear all of the NULL values.
    foreach ($payload as $key => $value) {
      if ($value === NULL) unset($payload[$key]);
    }

    return $payload;
  }

  /**
   * Render out the HTML version.
   */
  public function render ($channel_type, $channel_name) {
    $response = array();
    $response[] = '<div class="message">';
    $response[] = '<h1 class="' . $channel_type . '" channel-type="' . $channel_type . '">Channel: ' . $channel_name . '</h1>';
    $response[] = '<p>' . App::convertMarkup($this->text) . '</p>';
    $response[] = '<div class="attachments">';

    foreach ($this->attachments as $attachment) {
      $response[] = $attachment->render();
    }

    $response[] = '</div>';
    $response[] = '</div>';

    return implode('', $response);
  }

}