<?php
class pmurl {
	
	public function get_login_url(){
		return get_instance()->config->site_url('login');
	}
	
	public function get_template_image($image = ''){
		if($image == ''){
			return '';
		}
		
		$template = TEMPLATE;
		if ($template == 'default3') {
			$template_folder = base_url('/data/template/'.$template.'/src/assets/images');
		} else {
			$template_folder = base_url('/data/template/'.$template.'/images');
		}
		return $template_folder.'/'.$image;
	}
	
	public function get_template_image_path($image = ''){
		if($image == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = FCPATH.'data/template/'.$template.'/images';
		return $template_folder.'/'.$image;
	}
	
	public function get_css($stylesheet = ''){
		if($stylesheet == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = base_url('/data/template/'.$template.'/css');
		return $template_folder.'/'.$stylesheet;
	}
	
	public function get_css_data($stylesheet = ''){
		if($stylesheet == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = FCPATH . 'data/template/'.$template.'/css';
		return file_get_contents($template_folder.'/'.$stylesheet);
	}
	
	public function get_js($js = ''){
		if($js == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = base_url('/data/template/'.$template.'/js');
		return $template_folder.'/'.$js;
	}
	
	public function get_file_url($file, $path = '', $orig_file_name = ''){
		get_instance()->load->library('encrypt');
		$file_name = urlencode(base64_encode($path.$file));
		$orig_file_name = urlencode(base64_encode($orig_file_name));
		// TODO: USER LOGIN CHECK
		$username = get_instance()->session->userdata('username');
		$key = $username.'*^'.$path.$file;
		$key = urlencode(base64_encode(get_instance()->encrypt->encode($key)));
		return get_instance()->config->site_url('files/downloadfile/?file='.$file_name.'&key='.$key.'&orig_file_name='.$orig_file_name);
	}
		// function added by manish 
	public function get_vendor($vendor = ''){
		if($vendor == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = base_url('/data/template/'.$template.'/vendors');
		return $template_folder.'/'.$vendor;
	}

	public function get_jscss($url = ''){
		if($url == ''){
			return '';
		}
		
		$template = TEMPLATE;
		$template_folder = base_url('/data/template/'.$template);
		return $template_folder.'/'.$url;
	}

	public function get_all_data($path = '', $type = '', $pattern = ''){
		if($path == ''){
			return false;
		}

		$template = TEMPLATE;
		$dir = 'data/template/' . $template . '/' . $path . '/';
		$type = $pattern . '*.' . $type;
		$template_folder = $dir . $type;
		if (is_dir($dir)) {
			return glob($template_folder);
		};

		return false;
	}
}