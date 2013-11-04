<?php
class myMailer
{
	protected $mailer;
	protected $email;

	protected $name;
	protected $from;
	protected $to;
	protected $bcc = array();
	protected $subject;
	protected $message;

	/*________________________________________________________________________________________________________________*/
	function myMailer($name, $subject, $template = true)
	{
		$this->mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
		$this->email = Swift_Message::newInstance($subject);
		$this->name = $name;

		$c = new Criteria();
		$c->add(EmailPeer::NAME, $name);
		$email = EmailPeer::doSelectOne($c);

		if($template == true)
		{
			$c = new Criteria();
			$c->add(EmailPeer::NAME, "template");
			$template = EmailPeer::doSelectOne($c);

			$customerId = null;
			
			if (method_exists(sfContext::getInstance()->getUser(), "getCustomerId")) {
				$customerId = sfContext::getInstance()->getUser()->getCustomerId();
			}
			
			$path = CustomerPeer::getHeaderEmail($customerId);
			
			$this->message = str_replace(Array("**SUBJECT**", "**CONTAIN**", "**HEADER_BG**"), 
					Array(str_replace("[wikiPixel] ", "", $subject), $email->getMessage(), $path), 
					$template->getMessage());
		}
		else
			$this->message = $email->getMessage();

		$this->subject = $subject;
	}

	/*________________________________________________________________________________________________________________*/
	function setFrom($from, $force = false)
	{
		if ($force) {
			$from = Array(current($from) => current($from));
		}
		else {
			if(in_array($this->name, Array('invitation_send', 'send_user_access', 
					'forgot-password-step1', 'forgot-password-step2', 'send_password_api', 'activate_user', 
					'invitation_free_send', 'send_user_access_freemium', 
					'send_user_access_no_trial')))
				$from = Array(ConfigurationPeer::retrieveByType("default_signin_from_email")->getValue() => current($from));
			else
				$from = Array(ConfigurationPeer::retrieveByType("default_from_email")->getValue() => current($from));
		}

		$this->email->setFrom($from);
		$this->from = $from;
	}
	
	/*________________________________________________________________________________________________________________*/
	function setTo($to)
	{
		$this->to = $to;
	}

	/*________________________________________________________________________________________________________________*/
	function setBcc($bcc)
	{
		$this->bcc = $bcc;
	}
	
	/*________________________________________________________________________________________________________________*/
	function compose($search, $replace)
	{
		$this->message = str_replace($search, $replace, $this->message);
		$this->email->setBody($this->message, 'text/html');  
	}

	/*________________________________________________________________________________________________________________*/
	function send()
	{
		foreach($this->to as $key => $value)
		{
			$this->email->setTo(Array($key => $value));
			
			foreach($this->bcc as $key => $value)
			{
				$this->email->setBcc(Array($key = $value));
			}
			
			$flag = $this->mailer->send($this->email);
		}
		
		$this->email->setTo(Array("archives@wikipixel.fr" => "archives@wikipixel.fr"));
		$this->mailer->send($this->email);
	}
}
?>