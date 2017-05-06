<?php
/* 

###############################################################

  FILE CONNECTOR CLASS 
  
  This class is used as an abstraction layer in establishing 
  upload and file manipulation connections. 

################################################################
*/
class FileConnector {

  // CONTROL VARIABLES FOR THIS CONNECTOR
  protected $directory   = "";       // directory the files will be uploaded to, this must be a FULL PATH
  protected $accept      = array("*");   // only these mime-types will be accepted
  protected $max_size    = 0;      // default to PHP.ini max post size
  protected $filestream  = "";       // fileStream currently being handled
  protected $filepath    = NULL;      // Currently open file

  // UPLOADED FILE HISTORY FOR THIS SCRIPT RUN WILL BE STORED HERE
  public $stored_files    = array();

  // ERROR CODE HANDLING
  protected $status     = 0;
  protected $statusCodes   = array();
  
  // FILE TO CONNECT TO
  
  /* 
    CONSTRUTOR
    @param $directory   = files will be saved to this directory, must be full path
    @param $accept    = array of accepted mime types, leave blank or * for all types
    @param $max_size    = maximum size in bytes allowable for upload
  */
  public function __construct($directory="",$accept="",$max_size="") {

    // Set parent directory for uploads
    if(isset($directory) && $directory != "") {
      $this->directory = $directory;
    }

    // Set accepted mime-types for this upload connector
    if(isset($accept) && $accept != "" && count($accept) != 0) {
      $this->accept = $accept;
    } else {
      $this->accept = array("*");
    }

    // Set accepted maximum size for this upload connector
    if(isset($max_size) && $max_size != "" && count($max_size) != 0) {
      $this->max_size = $max_size;
    } else {
      $this->max_size = intval($this->iniReturnBytes(ini_get('post_max_size'))-1000);
    }

    // SET ERROR CODES
    $this->statusCodes[0] = "No Errors, Upload Connector is Idle";
    $this->statusCodes[1] = "File Upload Failed, the selected file could not be saved to the File System.";
    $this->statusCodes[2] = "File Upload Failed, the selected file is of an invalid file type.";
    $this->statusCodes[3] = "File Upload Failed, the selected file is greater than " . $this->max_size . " bytes";
    $this->statusCodes[4] = "File Rename Failed";

  }
  
  /*
    OPEN FILE
  */
  public function open($filepath) {
    if(file_exists($filepath)) { // check if exists
      if(!is_dir($filepath)) { // check if dir
        if(is_readable($filepath)) { // check if readable
          $this->filepath = $filepath; // set the file path pointer
        } else {
          $response->message = "File is not readable: [" . $filepath . "]";
          $response->status = false;  
        }
      } else {
        $response->message = "Specified path: [" . $filepath . "] is a directory.";
        $response->status = false;    
      }
    } else { // if file is not found, the connector will create it
      if(!is_dir($filepath)) { // check if dir
        $this->filepath = $filepath; // set the file path pointer
        $this->write(NULL); // create file
        
        $response->message = "File created: [" . $filepath . "]";
        $response->status = false;
      } else {
        $response->message = "Specified path: [" . $filepath . "] is a directory.";
        $response->status = false;    
      }
    }
  }
  
  /*
    CLOSE FILE
  */
  public function close() {
    $this->filepath = NULL; // close file path
  }
  
  /*
    WRITE TO CURRENTLY OPEN FILE
    @param $str = data to write to file
    @param $opts = options for file write
  */
  public function write($data,$opts=array()) {
    
    // set response vars
    $response = new StdClass();
    $response->status = true;
    
    $mode = "overwrite"; // default
    if(strtolower($opts["mode"]) == "append" || strtolower($opts["mode"]) == "overwrite") {
      $mode = strtolower($opts["mode"]);
    }
    
    // write file
    if(file_exists($this->filepath) && !is_writable($this->filepath)) { // check file locked
      $response->message = "File is not writable: [" . $this->filepath . "]";
      $response->status = false;
    } else { // attempt to write to file
      if($mode == "append") {
        if($lock == true) {
          file_put_contents($this->filepath,$data,FILE_APPEND);
        } else {
          file_put_contents($this->filepath,$data,FILE_APPEND | LOCK_EX);
        }
      } else {
        if($lock == true) {
          file_put_contents($this->filepath,$data);
        } else {
          file_put_contents($this->filepath,$data,LOCK_EX);
        }
      }
      if($success) {
        $response->message = "Successfully wrote to file: [" . $this->filepath . "]";
        $response->status = true;      
      } else {
        $response->message = "Could not write to file: [" . $this->filepath . "]";
        $response->status = false;  
      }
    }
      
    return $response;
    
  }
  
  /*
    APPEND TO CURRENTLY OPEN FILE
    @param $dir = directory to change to
  */
  public function append($data,$opts=array()) {
    $opts["mode"] = "append"; // overwrite whatever the mode is to append
    $this->write($data,$opts);
  }

  /*
    DELETE CURRENTLY OPEN FILE
  */
  public function delete() {
    
  }
  

  /* 
    CHANGE DIRECTORY
    @param $dir = directory to change to
  */
  public function changeDir($dir) {
    $this->directory = $dir;
  }

  /* 
    UPLOAD FILE
    @param $filestream = fileStream currently being handled
  */
  public function upload($filestream,$rename_file_to="") {

    // set vars
    $statusOut = false;
    $this->filestream = $filestream;

    // process file stream
    if($this->filestream != "none" or !is_null($this->filestream)) {

      // get file extension fpr later use
      $thisExt = substr($this->filestream['name'],strrpos($this->filestream['name'],".")+1);

      // rename the file if a name is specified
      if(!is_null($rename_file_to) && $rename_file_to != "") {
        // rename the file to the specified file name, we need to attach the extension
        $new_filename = $this->directory . "/" . $this->safeFilename($rename_file_to) . "." . $thisExt;
      } else {
        // otherwise rename file so that it's alpha-numeric
        $new_filename = $this->directory . "/" . $this->safeFilename($this->filestream['name']);
      }

      // CHECK TO SEE IF FILE HAS BEEN UPLOADED
      if(is_uploaded_file($this->filestream['tmp_name'])) { // Temp Upload Passed

        if($this->filestream['size'] <= $this->max_size) { // Size Passed
          
          // CHECK IF CORRECT MIME TYPE
          $typePassed = false;
          if($this->accept[0] == "*" || $this->accept[0] == "*/*") {
            $typePassed = true;
          } else {
            for($i=0; $i<count($this->accept); $i++) {
              if($this->filestream["type"] == $this->accept[$i]) {
                $typePassed = true;
              }
            }
          }

          // if mime type passed, upload the file
          if($typePassed) { // Type Passed

            if(move_uploaded_file($this->filestream['tmp_name'],$new_filename)) { // Move Passed
            
              // CHMOD FILE
              @chmod($new_filename, 0755); // Will not work on Windows boxes, so don't make the script rely on this.
              
              // PLUG THIS FILE'S INFO INTO THE FILE PROCESS LIST
              $index = intval(count($this->storedFiles));
              $this->storedFiles[$index]['fullname']   = $new_filename;
              $this->storedFiles[$index]['filename']   = substr($new_filename,strrpos($new_filename,"/")+1);
              $this->storedFiles[$index]['old_name']   = $this->filestream['name'];
              $this->storedFiles[$index]['tmp_name']  = $this->filestream['tmp_name'];
              $this->storedFiles[$index]['size']     = $this->filestream['size'];
              $this->storedFiles[$index]['type']     = $this->filestream["type"];
              $this->storedFiles[$index]['ext']     = $thisExt;

              // CONFIRM FILE STATUS
              $statusOut = true;
              $this->status = 0;
              
            } else { // Move Failed
              $this->status = 1;
            }

          } else { // Type Failed
            $this->status = 2;
            /* 
            if(is_file($new_filename)) {
              unlink($new_filename); // delete this file from the system so it doesn't junk it up
            } 
            */
          }

        } else { // Size Failed
          $this->status = 3;
          /* 
          if(is_file($new_filename)) {
            unlink($new_filename); // delete this file from the system so it doesn't junk it up
          } 
          */
        }
      }
    }

    // return status
    return $statusOut;
  }

  /* 
    UTILITY FUNCTION, RETURNS LAST STATUS CODE DESCRIPTION
  */
  public function getStatus() {
    return $this->statusCodes[$this->status];
  }

  /* 
    UTILITY FUNCTION, RETURNS LAST STATUS CODE
  */
  public function getStatusCode() {
    return $this->status;
  }

  /*
    RENAME FILE TO
    Renames a filename to the specified name
    @param $oldFile = name of file to rename
    @param $newName = new name of the file
    DEV NOTE: THIS WILL AUTOMATICALLY STRIP ILLEGAL CHARACTERS OUT OF THE NEW NAME
    DEV NOTE: THIS WILL AUTOMATICALLY ASSUME THE FILE YOU WANT TO RENAME IS IN THE STORED DIRECTORY
  */
  public function renameFile($oldFile,$newName) {

    // status out
    $output = false;

    // set up rename variables
    $oldFileName   = $this->directory . "/" . $oldFile;
    $new_filename   = $this->directory . "/" . $this->safeFilename($newName);

    // rename the file
    if(rename($oldFileName,$new_filename)) {
      $output  = true;
    } else { // rename failed
      $this->status = 4;
    }
		
    return $output;
  }

  // ############################### PRIVATE FUNCTIONS ##########################################

  /*
     RETURN INI SIZE IN BYTES
  */
  private function iniReturnBytes($val) {
    $val  = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    if($last == 'g') {
      $valOut = $val*1073741824;
    } else if($last == 'm') {
      $valOut = $val*1048576;
    } else if($last == 'k') {
      $valOut = $val*1024;
    }
    return $valOut;
  }

  /*
    SAFE FILE NAME
    Reformats a filename so that is contains only alpha-numeric characters and _-.
    @param $filename = name of file to rename
  */
  private function safeFilename($filename) {
    $new_filename = str_replace(" ", "-",$filename);
    $new_filename = ereg_replace("[^A-Za-z0-9_.-]", "",$new_filename);
    return $new_filename;
  }

}

?>