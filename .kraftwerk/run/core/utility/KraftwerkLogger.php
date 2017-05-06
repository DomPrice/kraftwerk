<?php
/* 
###############################################################################

  KRAFTWERK LOGGER CLASS
  
  This class generates the log file for Kraftwerk
  
###############################################################################
*/
class KraftwerkLogger extends FileConnector {
  
  // VARS
  public $logpath = NULL;

  // GENERATE LOG FILE IF NOT EXISTS
  public function generate($logpath) {
    global $kraftwerk; // generate can only be called after the core has loaded
    if(!file_exists($kraftwerk->get_log_dir())) {
      @mkdir($kraftwerk->get_log_dir(), 0777); // make log directory
    } else {
      @chmod($kraftwerk->get_log_dir(), 0777); // force writeable
    }
    $this->logpath = $logpath; // set log path
    $this->open($logpath);
  }
  
  // WRITE ERROR
  public function log_error($error) {
    $this->open($this->logpath);
    $this->append("[" . date("Y-m-d H:i:s T") . " / " . $_SERVER['REMOTE_ADDR'] . "] ERROR: " . "\r\n" . $error . "\r\n");
    $this->close();
  }
  
  // WRITE WARNING
  public function log_warning($warning) {
    $this->open($this->logpath);
    $this->append("[" . date("Y-m-d H:i:s T") . " / " . $_SERVER['REMOTE_ADDR'] . "] WARNING: ". "\r\n" . $warning . "\r\n");
    $this->close();
  }
  
  // WRITE GENERIC
  public function log_info($info) {
    $this->open($this->logpath);
    $this->append($info . "\r\n");
    $this->close();
  }
  
  // WRITE ERROR
  public function log_render($message) {
    global $kw_config;
    if(!isset($kwconfig->log_renders) || (isset($kwconfig->log_renders) && $kwconfig->log_renders != false)) {
      $this->open($this->logpath);
      $this->append("[" . date("Y-m-d H:i:s T") . " / " . $_SERVER['REMOTE_ADDR'] . "] OK: " . "\r\n" . $message . "\r\n");
      $this->close();
    }
  }
  
}
?>