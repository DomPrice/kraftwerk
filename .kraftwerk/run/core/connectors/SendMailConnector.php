<?php
/* 

###############################################################

  SEND MAIL CONNECTOR CLASS 
  
  This class is used as an abstraction layer in establishing 
  sendmail for the kraftwerk application

################################################################
*/
class SendMailConnector {

  // CONTROL VARIABLES FOR THIS CONNECTOR
  public $from     = NULL;       // from email for sendmail
  public $to       = NULL;       // to email for sendmail
  public $cc       = NULL;       // cc email for sendmail
  public $bcc      = NULL;       // bcc email for sendmail
  public $subject  = NULL;        // subject for sendmail
  public $reply_to = NULL;        // reply to for sendmail
  public $message  = NULL;        // message for sendmail
  public $html     = false;      // use HTML when sending mail
  
  // CONSTRUCTOR
  public function __construct() { }
  
  /* 
    SEND MAIL
    @returns true/fail depending on whether or no successful
  */
  public function send_mail() {
    
    // assemble headers
    $headers = array();
    
    if($this->html === true) {
      $headers[] = 'MIME-Version: 1.0';
      $headers[] = 'Content-type: text/html; charset=utf-8';
    }
    if($this->from != NULL && $this->from != "") {
      $headers[] = "From: " . $this->from;
    }
    if($this->cc != NULL && $this->cc != "") {
      $headers[] = "Cc: " . $this->cc;
    }
    if($this->bcc != NULL && $this->bcc != "") {
      $headers[] = "Bcc: " . $this->bcc;
    }
    if($this->subject != NULL && $this->subject != "") {
      $headers[] = "Subject: " . $this->subject;
    }
    if($this->from != NULL && $this->from != "") {
      $headers[] = "From: " . $this->from;
    }
    if($this->reply_to != NULL && $this->reply_to != "") {
      $headers[] = "Reply-To: " . $this->reply_to;
    }
    
    // attach mailer version
    $headers[] = "X-Mailer: PHP/".phpversion();

    // implode headers
    $send_headers = implode("\r\n", $headers);
  
    // try sending mail, and return responses
    $response = new StdClass();
    $response->status = mail($this->to,$this->subject,$this->message,$send_headers);
    $response->headers = $send_headers;
    return $response;
  }
}