<?php
namespace out;

class Command {
  public static function __callStatic($name, $args) {
    if(strpos($name, 'cmd') !== 0) {
      $name = 'cmd' . $name;
      self::$name($args);
    }
  }

  public static function cmdPing($args = null, $cmd) {
    $time = time() - $cmd->message->date;
    return \out\Message::auto("*Pong!* {$time}s", "Markdown");
  }

  public static function cmdPong($args = null, $cmd) {
    $time = time() - $cmd->message->date;
    return \out\Message::auto("*Ping!* -{$time}s", "Markdown");
  }

  public static function cmdHelp($args = null, $cmd = null) {
    return \out\Message::auto("Maybe, some day, I'll help you.", "Markdown");
  }


  // TODO: implement memes.json (https://gist.githubusercontent.com/22sk/92e7e0d2577ac3e1c167/raw/memes.json)
  public static function cmdMeme($args = null, $cmd = null) {
    $memes = json_decode(
      file_get_contents('https://gist.githubusercontent.com/22sk/92e7e0d2577ac3e1c167/raw/memes.json'), true
    );
    $name = str_clean($args);

    if(empty($args)) {
      Message::auto("Available memes: `".implode(", ", array_keys($memes))."`");
    } else if(array_key_exists($name, $memes)) {
      $types = json_decode(file_get_contents('types.json'));
      $type = $memes[$name]['type'];
      $method = $types->$type;
      $update = array($type => $memes[$name]['id']);
      Update::auto($update, $method);
    } else {
      Message::auto("Unknown meme! Use /meme to get a list of all available memes.");
    }
  }

  public static function cmdHost($args = null, $cmd = null) {
    return \out\Message::auto("Hoster: `".gethostname()."`", "Markdown");
  }

  /**
   * @param string|null $args
   * @param \in\Command|null $cmd
   */
  public static function cmdUser($args = null, $cmd = null) {
    $mysqli = db_connect();
    if(empty($args)) {
      if($cmd->message->reply_to_message != null) $user = $cmd->message->reply_to_message->from;
      else $user = $cmd->message->from;

      $user = new \in\User($user);
      \out\Message::auto(
        "Username: @{$user->getUsername()}\n".
        "First name: `{$user->getFirstName()}`\n".
        "Last name: `{$user->getLastName()}`\n".
        "User ID: `{$user->getID()}`\n",
        "Markdown"
      );
    } else {
      if (intval($args)) {
        $id = intval($args);
        $result = $mysqli->query("SELECT * FROM userdata WHERE id = {$id}");
      } else {
        $result = $mysqli->query("SELECT * FROM userdata WHERE LOWER(username) = LOWER('{$args}')");
      }
      if (mysqli_num_rows($result) > 0) {
        $result = mysqli_fetch_assoc($result);
        \out\Message::auto(
          "Username: @{$result['username']}\n" .
          "First name: `{$result['first_name']}`\n" .
          "Last name: `{$result['last_name']}`\n" .
          "User ID: `{$result['id']}`\n" .
          "Last updated: `{$result['last_updated']}`\n",
          "Markdown"
        );
      } else \out\Message::auto("Unknown user.");
    }
  }
}
