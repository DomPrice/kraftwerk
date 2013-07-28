<?php
/* 

###########################################################

  KRAFTWERK VIEW CLASS 
	
  This is the control class for the kraftwerk views
	
###########################################################
*/
class KraftwerkView {


	public function __construct() {
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
