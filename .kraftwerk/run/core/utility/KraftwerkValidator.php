<?php
/* 
###############################################################################

  KRAFTWERK VALIDATOR CLASS
  
  Used with Kraftwerk Model to provide an interface for validation
  
###############################################################################
*/
class KraftwerkValidator  {
  
  /*
     stores validation relationships
     $val_rel["variable_name"] = format
  */
  protected $val_rel = array();
  
  /*
    Validator Constructor
  */
  public function __construct() { }
  
  /*
    Store a validation relationship
  */
  public function add_format($var_name,$format) {
    $this->val_rel["$var_name"] = $format;  
  }
  
  /*
    Alias for add_format
  */
  public function add($var_name,$format) {
    $this->add_format($var_name,$format);  
  }
  
  /*
    Get list of fields to validate
  */
  public function get_formats() {
    return $this->val_rel;
  }
  
}
?>