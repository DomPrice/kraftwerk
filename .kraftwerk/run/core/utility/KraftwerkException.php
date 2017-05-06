<?php
/* 
###############################################################################

  KRAFTWERK EXCEPTION CLASS
  
  Extends PHP's exception class to include kraftwerk framework related 
  error handling.
  
###############################################################################
*/
class KraftwerkException extends Exception {

  /*
    Exception Handler Constructor
  */
  public function __construct() { }
  
  /*
    Throw Error 
  */
  public function throw_error($error_message,$e="") {
    global $kraftwerk;
    global $kw_config;
    if(isset($kw_config->error_page) && $kw_config->error_page != NULL && $kw_config->error_page != "") { // make sure we don't have a custom error page
      header("Location: /" . $kw_config->error_page);
      exit;
    } else { // use kraftwerk's default error handling page
      $error_template = $kraftwerk->ASSETS_DIR . "/error_template.php";
      $error_template_logo = "/kwlogo.png";
      $error_stacktrace = $e;
      if(file_exists($error_template)) {
        try {
          include_once($error_template);
          exit;
        } catch(Exception $e) {
          die("Error template not found, rendering text only error message: [" . $error_message . "]");
        }
      } else {
        die($error_message);
      }
    }
  }
}
?>