<?php
class pmfiles {
	
	public function get_file_icon($file_id){
		$file = get_instance()->db->get_where('files_files', array('id' => $file_id))->row_array();
		$file_name = $file['file_name'];
		$extension = substr(strrchr($file_name,'.'),1);
		$image = get_instance()->pmurl->get_template_image('files/icons/'.$extension.'.png');
		$image_path = get_instance()->pmurl->get_template_image_path('files/icons/'.$extension.'.png');
		if(!file_exists($image_path)){
			$image = get_instance()->pmurl->get_template_image('files/icons/txt.png');
		}
		return $image;
	}
	
}