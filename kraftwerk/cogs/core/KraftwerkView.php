<?php
/* 

#####################################################
	KRAFTWERK PARENT CLASS 
	
	This is the control class for the kraftwerk application


#####################################################
*/
class KraftwerkView {

	// CLASS VARIABLES
	var $CLASSVERSION 		= '0.0.3';
	var $CLASSDATE			= '08/08/2010';
	var $CLASSAUTHORS		= 'Dom Price';
	var $CONFIG_LAYOUT_DIR 	= '';

	// CONFIG STRUCT
	var $CONFIG 			= array();	
	var $CONSTANTS 			= array();	
	
	// OTHER STRUCTS
	var $VIEWS_LOADED 	= array();

	// LAYOUT DATA FOR LATER PARSING
	var $USE_LAYOUT			= "";
	var $LAYOUT_DATA		= "";

	// ESTABLISH NEW DATABASE CONNECTOR FOR THE LOGIN SYSTEM TO USE
	protected $viewConnector = "";

	// CREATE NEW UTILITY CLASS
	protected $utilities = "";

	/* 
		CONSTRUTOR
	*/
	public function __construct($loaded_layouts=array()) {

		// ESTABLISH LOGIN CONNECTOR, WE NEED TO GRAB THE MASTER DATABASE DATA FROM THE $GLOBAL ENVIRONMENT
		$login_user 	= $GLOBALS['kraftwerk']->CONFIG["content_database_username"];
		$login_pass 	= $GLOBALS['kraftwerk']->CONFIG["content_database_password"]; 
		$login_host 	= $GLOBALS['kraftwerk']->CONFIG["content_database_server"];
		$login_schema 	= $GLOBALS['kraftwerk']->CONFIG["content_database_schema"];
		$this->viewConnector = new DatabaseConnector($login_host, $login_user, $login_pass, $login_schema);

		// INITIALIZE UTILITY CLASS FOR COMMON FUNCTIONS
		$this->utilities = new KraftwerkUtility();

		// LOAD THE VIEWS 
		$this->loadViews($loaded_layouts);

	}

	/* 
		LOAD VIEWS
		Loads the global layouts
		VOID
	*/
	public function loadViews($layouts) {
		$this->VIEWS_LOADED = $layouts;
	}

	/* 
		GET VIEWS
		Retrieves the global layouts
	*/
	public function getViews() {
		return $this->VIEWS_LOADED;
	}

	/* 
		USE LAYOUT
		Use this layout for content
		VOID
	*/
	public function useLayout($layout) {
		$this->USE_LAYOUT = $layout;
	}

	/* 
		PARSE CONTENT
		Parse the data
		VOID
	*/
	public function parseContent($content) {
		$CONTENT = $content; // load content data
		$thisLayout  = $this->VIEWS_LOADED[$this->USE_LAYOUT];
		include_once($thisLayout);
	}

	
}
?>