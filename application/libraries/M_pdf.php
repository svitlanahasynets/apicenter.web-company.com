<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
include_once APPPATH.'/third_party/mpdf/mpdf.php';
 
class M_pdf {
 
    public $params;
    public $pdf;
 
    public function __construct($params = 'A4-L')
    {
        $this->params =$params;
    }
    
    public function createPDF($attrs = null){
	    $instance = get_instance();
	    $instance->load->library('Pmexport');
	    $defaultAttrs = $instance->pmexport->getPdfSettings();
	    global $pdfMargins;
	    if(isset($pdfMargins) && !empty($pdfMargins)){
		    $defaultAttrs = array_merge($defaultAttrs, $pdfMargins);
	    }
	    
		if($attrs != null){
		    $attrs = array_merge($defaultAttrs, $attrs);
		} else {
			$attrs = $defaultAttrs;
		}
	    $this->params = $attrs;
	    
		$pdf = new mPDF('c', $attrs['format'].'-'.$attrs['orientation'], $attrs['defaultFontSize'], $attrs['defaultFont'], $attrs['marginLeft'], $attrs['marginRight'], $attrs['marginTop'], $attrs['marginBottom']);

		$pdf->params = $attrs;
		return $pdf;
    }
}