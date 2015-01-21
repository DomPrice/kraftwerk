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
	public function send($mailer_vars) {
		global $kraftwerk;
		global $kw_config;
		
		// SAVE RENDER VARS CONTAINER
		global $variables;
		$variables = new stdClass;

		// REGISTER GLOBALS
		if($mailer_vars != NULL && $mailer_vars != "" && count($mailer_vars) > 0) {
			foreach($mailer_vars as $key => $value) {
				if($variables->$key == "" || $variables->$key == NULL) {
					$variables->$key = $value;
				}
			}
		}
		
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
		try {
			
			// try to send
			$mailer_response = $this->send_mail();
			
			// log warning
			if($mailer_response->status != true) {
				$warning = "Kraftwerk was unable to send email.\r\nHeaders:\r\n" . $mailer_response->status;	
				$kraftwerk->logger->log_warning($warning);
			}
			
			return $mailer_response->status;
			
		} catch(Exeception $e) {
			$error = "Kraftwerk was unable to send email. A sendmail error occured: " . $e;	
			$kraftwerk->logger->log_error($error);
			$kraftwerk->exception->throw_error($error,$e);	
		}
	}
	
}
?>