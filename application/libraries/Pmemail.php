<?php
class pmemail {
	
	public function send_email($html, $recipient, $subject, $attachments = array(), $inlineAttachments = array()){
		$CI =& get_instance();
		$CI->load->library('email');
		$config = Array(
			'protocol' => "smtp",
			'smtp_host' => SMTP_HOST,
			'smtp_port' => SMTP_PORT,
			'smtp_timeout' => 5,
			'mailtype' => "html",
			'charset' => "utf-8",
			'wordwrap' => FALSE,
		);
		
		if(SMTP_CRYPTO != ''){
			$config['smtp_crypto'] = SMTP_CRYPTO;
		}
		
		if(SMTP_USER != '' && SMTP_PASS != ''){
			$config['smtp_user'] = SMTP_USER;
			$config['smtp_pass'] = SMTP_PASS;
		}
		
		$CI->email->initialize($config);
		
		$CI->email->from(FROM_EMAIL, FROM_NAME);
		$CI->email->to($recipient); 
		
		$CI->email->subject($subject);
		
		foreach($attachments as $attachment){
			if(file_exists($attachment)){
				$CI->email->attach($attachment);
			}
		}
		
		foreach($inlineAttachments as $key => $attachment){
			if(file_exists($attachment)){
				$CI->email->attach($attachment, 'inline');
				$cid = $CI->email->attachment_cid($attachment);
				$html = str_replace('%7B'.$key.'%7D', 'cid:'.$cid, $html);
			}
		}
		
		$CI->email->message($html);
		
		$CI->email->send();
		$CI->email->clear(true);
		
		return true;
	}
	
}