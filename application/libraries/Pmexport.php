<?php
class pmexport {
	
	public function download_csv($columns, $rows){
		$finalRows = array();
		foreach($rows as $row){
			$finalRow = array();
			foreach($columns as $key => $columnName){
				if(array_key_exists($key, $row)){
					$value = $row[$key];
					if(strpos($key, 'date') > -1){
						$value = format_date($value);
					}
					$finalRow[$key] = $value;
				}
			}
			$finalRows[] = $finalRow;
		}

		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=data.csv');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		
		// output the column headings
		fputcsv($output, $columns);
		
		// loop over the rows, outputting them
		foreach($finalRows as $row){
			fputcsv($output, $row);
		}
		return;
	}
	
	public function includePdfBackground($pdf){
		$params = array();
		if(isset($pdf->params)){
			$params = $pdf->params;
		}
		
		$instance = get_instance();
		$instance->load->model('Settings_model');
		
		if(isset($params['orientation']) && $params['orientation'] == 'L'){
			$backgroundImage = $instance->Settings_model->getValue('general', 'pdf_background_landscape');
		} else {
			$backgroundImage = $instance->Settings_model->getValue('general', 'pdf_background_portrait');
		}
		if($backgroundImage != ''){
			$backgroundImageLocation = $instance->Settings_model->getFilePath($backgroundImage);
			$pdf->SetWatermarkImage($backgroundImageLocation, 1, 'P', array(0,0));
			$pdf->watermarkImgBehind = true;
			$pdf->showWatermarkImage = true;
		}
		return $pdf;
	}
	
	public function getPdfSettings(){
		$settings = array();
		$instance = get_instance();
		$instance->load->model('Settings_model');
		
		$settings['format'] = $instance->Settings_model->getValue('general', 'pdf_format');
		$settings['orientation'] = $instance->Settings_model->getValue('general', 'pdf_orientation');
		$settings['marginLeft'] = $instance->Settings_model->getValue('general', 'pdf_margin_left');
		$settings['marginRight'] = $instance->Settings_model->getValue('general', 'pdf_margin_right');
		$settings['marginTop'] = $instance->Settings_model->getValue('general', 'pdf_margin_top');
		$settings['marginBottom'] = $instance->Settings_model->getValue('general', 'pdf_margin_bottom');
	    $settings['defaultFontSize'] = '';
	    $settings['defaultFont'] = '';
	    
		return $settings;
	}
	
}