<?php

use Agnate\RPG\Dispatcher\DispatcherInterface;

namespace Agnate\RPG\Dispatcher;

class SlackDispatcher implements DispatcherInterface {
  
  public $connection;

  /**
   * Initialize the message dispatcher with the connection to use.
   */
  function __construct() {}

  /**
   * Dispatch the message to Slack (or our debug tool).
   */
  public function dispatch (\Agnate\RPG\Message $message) {
    if (empty($this->connection)) throw new \Exception ("SlackDispatcher requires a ServerConnection to dispatch a message.");

    // Get message as JSON.
    $messages = array();

    // If this is a global message, send one to each connection channel.
    if ($message->channel->type == \Agnate\RPG\Message\Channel::TYPE_PUBLIC) {
      foreach ($this->connection->public_channels as $channel_id => $channel_name) {
        $message->slack_channel = $channel_id;
        $messages[] = $message->jsonSerialize();
      }
    }
    else if ($message->channel->type == \Agnate\RPG\Message\Channel::TYPE_DIRECT) {
      // Loop through all Guilds and make a message copy.
      foreach ($message->channel->guilds as $guild) {
        $message->slack_channel = $this->connection->getGuildChannel($guild);
        $messages[] = $message->jsonSerialize();
      }
    }

    // Send the messages through the deliverer (to Slack).
    foreach ($messages as $msg) {
      // $payload = json_encode($msg, JSON_UNESCAPED_UNICODE);
      $response = $this->connection->commander->execute('chat.postMessage', $msg);
      $body = $response->getBody();

      // If the message errored, log it.
      if (empty($body['ok'])) throw new \Exception ('SlackDispatcher failed the chat.postMessage for the following Message: ' . var_export($body, true) . "\n\nMessage: " . var_export($msg, TRUE));
    }

    // Messages sent successfully.
    return TRUE;
  }

}