<?php

if(!function_exists('translate')){
	function translate($string = '', $vars = null, $language = 'dutch'){
		// Look for core translation php file in /lang folder
		$core_php_file_location = FCPATH.'application/language/'.$language.'/core.lang.php';

		if (file_exists($core_php_file_location)) {
			unlink($core_php_file_location);
		}

		if(!file_exists($core_php_file_location)){

			$core_csv_file_location = FCPATH.'application/language/'.$language.'/core.csv';
			$csv = csv_to_array($core_csv_file_location);
			$phpData = "<?php \n";
			$phpData .= '$lang = array();'."\n";
			foreach($csv as $originalString => $translatedString){
				$originalString = str_replace('"', '\\"', $originalString);
				$originalString = str_replace('\'', '`', $originalString);
				

				$translatedString = str_replace('\'', '\\\'', $translatedString);

				if ($_SESSION['default_lang'] == 'dutch') {
					$phpData .= '$lang["'.$originalString.'"] = \''.$translatedString.'\';'."\n";
				} else if ($_SESSION['default_lang'] == 'english') {					
					$phpData .= '$lang["'.$originalString.'"] = \''.$originalString.'\';'."\n";
				}				
			}

			file_put_contents($core_php_file_location, $phpData);
		}
		
/*
		// Add untranslated strings to base file
		$core_csv_file_location = FCPATH.'application/language/'.$language.'/base.csv';
		$contents = file_get_contents($core_csv_file_location);
		$translation_strings = explode("\n", $contents);
		if(!in_array($string, $translation_strings)){
			$contents .= "\n".$string;
			file_put_contents($core_csv_file_location, $contents);
		}
*/
		
		$string = str_replace('% ', '%% ', $string);
		if(file_exists($core_php_file_location)){
			require($core_php_file_location);
			if(array_key_exists($string, $lang) && $lang[$string] != ''){
				if(is_array($vars)){
					return vsprintf($lang[$string], $vars);
				} elseif($string != null){
					return sprintf($lang[$string], $vars);
				} else {
					return $lang[$string];
				}
			}
		} else {
			if(is_array($vars)){
				return vsprintf($string, $vars);
			} elseif($string != null){
				return sprintf($string, $vars);
			} else {
				return $string;
			}
		}

		if(is_array($vars)){
			return vsprintf($string, $vars);
		} elseif($string != null){
			return sprintf($string, $vars);
		} else {
			return $string;
		}

	}
}

function csv_to_array($filename='', $delimiter=',')
{
	$data = array();
	if (($handle = fopen($filename, "r")) !== FALSE) {
	    while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
	    	$originalString = $row[0];
	    	if (isset($row[1])) {
	    		$data[$originalString] = $row[1];
	    	}
	    }
	    fclose($handle);
	}
	return $data;
}

if(!function_exists('set_error_message')){
	function set_error_message($error = ''){
		// Get existing messages
		$error_messages = get_instance()->session->userdata('error_messages');

		// Check if session data already exists, else create an array
		if($error_messages == ''){
			$error_messages = array();
		}
		
		// Translate the error message
		$error_messages[] = translate($error);
		
		// Update session, add error message
		get_instance()->session->set_userdata('error_messages', $error_messages);
		
		return;
	}
}

if(!function_exists('get_error_messages')){
	function get_error_messages(){
		// Get all error messages
		$error_messages = get_instance()->session->userdata('error_messages');

		// Reset error messages
		get_instance()->session->set_userdata('error_messages', array());
		
		return $error_messages;
	}
}

if(!function_exists('set_success_message')){
	function set_success_message($string = ''){
		// Get existing messages
		$success_messages = get_instance()->session->userdata('success_messages');

		// Check if session data already exists, else create an array
		if($success_messages == ''){
			$success_messages = array();
		}
		
		// Translate the success message
		$success_messages[] = translate($string);
		
		// Update session, add error message
		get_instance()->session->set_userdata('success_messages', $success_messages);
		
		return;
	}
}

if(!function_exists('get_success_messages')){
	function get_success_messages(){
		// Get all success messages
		$success_messages = get_instance()->session->userdata('success_messages');

		// Reset success messages
		get_instance()->session->set_userdata('success_messages', array());
		
		return $success_messages;
	}
}

if(!function_exists('set_notice')){
	function set_notice($string = ''){
		// Get existing messages
		$notices = get_instance()->session->userdata('notices');

		// Check if session data already exists, else create an array
		if($notices == ''){
			$notices = array();
		}
		
		// Translate the success message
		$notices[] = translate($string);
		
		// Update session, add error message
		get_instance()->session->set_userdata('notices', $notices);
		
		return;
	}
}

if(!function_exists('get_notices')){
	function get_notices(){
		// Get all success messages
		$notices = get_instance()->session->userdata('notices');

		// Reset success messages
		get_instance()->session->set_userdata('notices', array());
		
		return $notices;
	}
}