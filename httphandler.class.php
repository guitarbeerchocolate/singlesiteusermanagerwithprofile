<?php
require_once 'classes/autoload.php';
class httphandler
{
  private $getObject;
  private $postObject;
  private $fileObject;
  private $webpath;
  private $config;

  function __construct($get = NULL, $post = NULL, $file = NULL)
  {
    $this->config = new config;
    if(!empty($get))
    {
     $this->getObject = (object) $get;
     $this->checkForGetWebPath();
     $this->checkForGetMethod();
    }
    if(!empty($post))
    {
      $this->postObject = (object) $post;
      $this->checkForPostWebPath();
      if(!empty($file))
      {
        $this->fileObject = $file;
      }
      $this->checkForPostMethod();
    }
  }

  private function checkForGetMethod()
  {
    if($this->getObject->method && (method_exists($this, $this->getObject->method)))
    {
      $evalStr = '$this->'.$this->getObject->method.'();';
      eval($evalStr);
    }
    else
    {
      $oStr = 'Invalid method supplied';
      if($this->webpath)
      {
        header('Location:'.$this->webpath.'?message='.urlencode($oStr));
      }
      else
      {
        echo $oStr;
      }
    }
  }

  private function checkForPostMethod()
  {
    if($this->postObject->method && (method_exists($this, $this->postObject->method)))
    {
      $evalStr = '$this->'.$this->postObject->method.'();';
      eval($evalStr);
    }
    else
    {
      $oStr = 'Invalid method supplied';
      if($this->webpath)
      {
        header('Location:'.$this->webpath.'?message='.urlencode($oStr));
      }
      else
      {
        echo $oStr;
      }
    }
  }

  private function checkForGetWebPath()
  {
    if(isset($this->getObject->webpath))
    {
      $this->webpath = urldecode($this->getObject->webpath);
    }
  }

  private function checkForPostWebPath()
  {
    if(isset($this->postObject->webpath))
    {
      $this->webpath = urldecode($this->postObject->webpath);
    }
  }

  /* User functions here */
  function login()
  {
    $auth = new authenticate;
    $result = $auth->login($this->postObject->username, $this->postObject->password);
    $headerString = 'Location:'.$result;
    if($result == NULL)
    {
      $headerString .= 'index.php?message=';
      $headerString .= urlencode('Who are you?');
    }
    else
    {
      $headerString .= '?message=';
      $headerString .= urlencode('Welcome');
    }
    session_regenerate_id(true);
    header($headerString);
    session_write_close();
  }

  function requestpasswordreset()
  {
    $auth = new authenticate;
    $result = $auth->requestpasswordreset($this->postObject->username);
    $headerString = 'Location:';
    if(isset($result->id))
    {
      $email = new electronicmail;
      $email->to = $result->username;
      $email->from = $this->config->values->MAILBOX_NAME;
      $email->subject = 'Password reset';
      $email->textmessage = 'Please use the following link to reset your password'.PHP_EOL;
      $email->textmessage .= $this->config->values->WEB_LOCATION;
      $email->textmessage .= 'resetpasswordform?tprid='.$result->id.'&usr='.$result->username.'&pwd='.$result->password;
      $response = $email->sendemail();
      $headerString .= 'index.php?message=';
      $headerString .= urlencode('A message has been sent your your email account.');
    }
    else
    {
      $headerString .= 'index.php?message=';
      $headerString .= urlencode('User unknown');
    }
    header($headerString);
  }

  function resetpassword()
  {
    $auth = new authenticate;
    $result = $auth->resetpassword($this->postObject->id, $this->postObject->username, $this->postObject->password);
    $headerString = 'Location:';
    $headerString .= 'index.php?message=';
    $headerString .= urlencode('Password reset');
    header($headerString);
  }

  function selfregister()
  {
    $auth = new authenticate;
    $session = new session;
    $nextPage = 'index.php';
    $headerString = 'Location:';

    /* Check if the passwords match */
    if($auth->passwordmatch($this->postObject->password1, $this->postObject->password2) == FALSE)
    {
      $headerString .= 'index.php?message=';
      $headerString .= urlencode('Passwords do not match');
    }
    else
    {
      /* Check if the user already exists */
      if($auth->userAlreadyExists($this->postObject->username) == TRUE)
      {
        $headerString .= 'index.php?message=';
        $headerString .= urlencode('User already exists');
      }
      else
      {
        $nextPage = $auth->selfregister($this->postObject->username, $this->postObject->password1);
        if(strtolower($nextPage) == 'index.php')
        {
          $headerString .= 'index.php?message=';
          $headerString .= urlencode('You are on the waiting list');
        }
        else
        {
          $headerString .= $nextPage;
        }
      }
    }
    session_regenerate_id(true);
    header($headerString);
    session_write_close();
  }

  function adminchanges()
  {
    $session = new session;
    $session->setUserSession($this->postObject->userid, $this->postObject->username);
    $this->config->updateini($this->postObject);
    session_regenerate_id(true);
    $url = 'private.php?sessionid='.$this->postObject->sessid;
    header('Location:'.$url);
    session_write_close();
  }

  function profile()
  {
    $session = new session;
    $session->setUserSession($this->postObject->userid, $this->postObject->username);
    if(!empty($this->fileObject['photo']['type']))
    {
      $type = $this->fileObject['photo']['type'];
      switch(strtolower($type))
      {
          case 'image/jpeg':
              $image = imagecreatefromjpeg($this->fileObject['photo']['tmp_name']);
              break;
          case 'image/png':
              $image = imagecreatefrompng($this->fileObject['photo']['tmp_name']);
              break;
          case 'image/gif':
              $image = imagecreatefromgif($this->fileObject['photo']['tmp_name']);
              break;
          default:
              exit('Unsupported type: '.$type);
      }
      // Target dimensions
      $max_width = 75;
      $max_height = 100;

      // Get current dimensions
      $old_width  = imagesx($image);
      $old_height = imagesy($image);

      // Calculate the scaling we need to do to fit the image inside our frame
      $scale      = min($max_width/$old_width, $max_height/$old_height);

      // Get the new dimensions
      $new_width  = ceil($scale*$old_width);
      $new_height = ceil($scale*$old_height);

      // Create new empty image
      $new = imagecreatetruecolor($new_width, $new_height);

      // Resize old image into new
      imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);

      // Catch the imagedata
      ob_start();
      imagejpeg($new, NULL, 90);
      $image = ob_get_clean();
      $photo = addslashes($image);
    }
    else
    {
      $photo = NULL;
      $type = NULL;
    }
    session_regenerate_id(true);
    $profile = new profile;
    $profile->update($this->postObject, $photo, $type);
    $url = 'private';
    header('Location:'.$url);
    session_write_close();
  }
}
new httphandler($_GET, $_POST, $_FILES);
?>