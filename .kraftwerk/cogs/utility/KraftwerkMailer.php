<?php
/* 
###############################################################################

  KRAFTWERK MAILER CLASS
	
  This is the control class for kraftwerk mailer handler
	
###############################################################################
*/
class KraftwerkMailer extends SendMailConnector {
	
	// 
	public $vars = array();
	public $template = "";
	
	/*
		USE TEMPLATE
		specifies which php template to use for send mail messages
	*/
	public function use_template($template) {
		$this->template = $template;
	}
	
	/*
		SEND MAIL
		sends the email, extends send_mail from underlying class
		renders the email according to the template
		@param $vars = variables to be passed to the template for parsing
	*/
	public function send($vars) {
		
		// RENDER MAILER TEMPLATE
		$template_path = realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $kraftwerk->VIEWS_DIR . "/_layouts/_mailers/" . $this->template . ".php";
		if(file_exists($template_path)) {
			ob_start();
			include_once($template_path);
			$this->message = ob_get_clean(); // send rendered message to message container
		} else {
			if($this->template == "") {
				$error = "Kraftwerk cannot find a template associated with this mailer. Please check your controller to verify a template has been specified.";	
				$kraftwerk->logger->log_error($error);
				$kraftwerk->exception->throw_error($error);	
			} else {
				$error = "Kraftwerk cannot find the specified mailer template file [" . $this->template . "]";	
				$kraftwerk->logger->log_error($error);
				$kraftwerk->exception->throw_error($error);	
			}
		}
		
		// SEND THE MESSAGE
		return $this->send_mail();
		
	}
	
}
?>