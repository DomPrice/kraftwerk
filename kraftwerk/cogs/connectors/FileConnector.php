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
	protected $directory 		= ""; 			// directory the files will be uploaded to, this must be a FULL PATH
	protected $accept			= array("*"); 	// only these mime-types will be accepted
	protected $maxSize			= 0;			// default to PHP.ini max post size
	protected $fileStream		= ""; 			// fileStream currently being handled

	// UPLOADED FILE HISTORY FOR THIS SCRIPT RUN WILL BE STORED HERE
	public $storedFiles			= array();

	// ERROR CODE HANDLING
	protected $status 		= 0;
	protected $statusCodes 	= array();

	/* 
		CONSTRUTOR
		@param $directory 	= files will be saved to this directory, must be full path
		@param $accept		= array of accepted mime types, leave blank or * for all types
		@param $maxSize		= maximum size in bytes allowable for upload
	*/
	public function __construct($directory="",$accept="",$maxSize="") {

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
		if(isset($maxSize) && $maxSize != "" && count($maxSize) != 0) {
			$this->maxSize = $maxSize;
		} else {
			$this->maxSize = intval($this->iniReturnBytes(ini_get('post_max_size'))-1000);
		}

		// SET ERROR CODES
		$this->statusCodes[0] = "No Errors, Upload Connector is Idle";
		$this->statusCodes[1] = "File Upload Failed, the selected file could not be saved to the File System.";
		$this->statusCodes[2] = "File Upload Failed, the selected file is of an invalid file type.";
		$this->statusCodes[3] = "File Upload Failed, the selected file is greater than " . $this->maxSize . " bytes";
		$this->statusCodes[4] = "File Rename Failed";

	}

	/* 
		CHANGE DIRECTORY
		@param $dir = directory to change to
	*/
	public function changeDir($dir) {
		$this->directory = $dir;
	}

	/* 
		CHANGE DIRECTORY
		@param $fileStream = fileStream currently being handled
	*/
	public function upload($fileStream,$renameFileTo="") {

		// set vars
		$statusOut		= false;
		$this->fileStream = $fileStream;

		// process file stream
		if($this->fileStream != "none" or !is_null($this->fileStream)) {

			// get file extension fpr later use
			$thisExt = substr($this->fileStream['name'],strrpos($this->fileStream['name'],".")+1);

			// rename the file if a name is specified
			if(!is_null($renameFileTo) && $renameFileTo != "") {
				// rename the file to the specified file name, we need to attach the extension
				$newFilename = $this->directory . "/" . $this->safeFilename($renameFileTo) . "." . $thisExt;
			} else {
				// otherwise rename file so that it's alpha-numeric
				$newFilename = $this->directory . "/" . $this->safeFilename($this->fileStream['name']);
			}

			// CHECK TO SEE IF FILE HAS BEEN UPLOADED
			if(is_uploaded_file($this->fileStream['tmp_name'])) { // Temp Upload Passed

				if($this->fileStream['size'] <= $this->maxSize) { // Size Passed
					
					// CHECK IF CORRECT MIME TYPE
					$typePassed = false;
					if($this->accept[0] == "*" || $this->accept[0] == "*/*") {
						$typePassed = true;
					} else {
						for($i=0; $i<count($this->accept); $i++) {
							if($this->fileStream["type"] == $this->accept[$i]) {
								$typePassed = true;
							}
						}
					}

					// if mime type passed, upload the file
					if($typePassed) { // Type Passed

						if(move_uploaded_file($this->fileStream['tmp_name'],$newFilename)) { // Move Passed
						
							// CHMOD FILE
							@chmod($newFilename, 0755); // Will not work on Windows boxes, so don't make the script rely on this.
							
							// PLUG THIS FILE'S INFO INTO THE FILE PROCESS LIST
							$index = intval(count($this->storedFiles));
							$this->storedFiles[$index]['fullname'] 	= $newFilename;
							$this->storedFiles[$index]['filename'] 	= substr($newFilename,strrpos($newFilename,"/")+1);
							$this->storedFiles[$index]['old_name'] 	= $this->fileStream['name'];
							$this->storedFiles[$index]['tmp_name']	= $this->fileStream['tmp_name'];
							$this->storedFiles[$index]['size'] 		= $this->fileStream['size'];
							$this->storedFiles[$index]['type'] 		= $this->fileStream["type"];
							$this->storedFiles[$index]['ext'] 		= $thisExt;

							// CONFIRM FILE STATUS
							$statusOut = true;
							$this->status = 0;
							
						} else { // Move Failed
							$this->status = 1;
						}

					} else { // Type Failed
						$this->status = 2;
						/* 
						if(is_file($newFilename)) {
							unlink($newFilename); // delete this file from the system so it doesn't junk it up
						} 
						*/
					}

				} else { // Size Failed
					$this->status = 3;
					/* 
					if(is_file($newFilename)) {
						unlink($newFilename); // delete this file from the system so it doesn't junk it up
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
		$output			= false;

		// set up rename variables
		$oldFileName 	= $this->directory . "/" . $oldFile;
		$newFileName 	= $this->directory . "/" . $this->safeFilename($newName);

		// rename the file
		if(rename($oldFileName,$newFileName)) {
			$output	= true;
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
    	$val 	= trim($val);
   		$last 	= strtolower($val[strlen($val)-1]);
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
	private function safeFilename($fileName) {
		$newFileName = str_replace(" ", "-",$fileName);
		$newFileName = ereg_replace("[^A-Za-z0-9_.-]", "",$newFileName);
    	return $newFileName;
	}

}

?>