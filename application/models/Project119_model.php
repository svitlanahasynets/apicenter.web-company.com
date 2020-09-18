<?php
class Project119_model extends CI_Model {

    public $projectId;
    
    protected $fiedls = [
        'DIGITALE_K' => 'UF92341314CF38BFA5E838A60E86A4028',
        'HR_NIEUWSB' => 'U6160CBAA487619D3D0930C96869D909A',
        'OR_NIEUWSB' => 'U130082194704641885296B8EA10852B7',
        'OVERIGE_NI' => 'UC924EBAA4B5D629C652E98816B064BEE',
        'EOR_NIEUWS' => 'U92DD5E8446387151F37476B652F811ED',
        'RABOBANK_N' => 'UFF10A9764D33291AA1EC71A2FF6FD400'
    ];

    function __construct()
    {
        parent::__construct();
        $this->projectId = 119;
    }

    public function getcustomerData($customerData)
    {
        $tmp    = $customerData;
        $merges = $customerData['merges'];

        foreach ($merges as $key=>$val) {
            if (isset($this->fiedls[$key])) {
                $merges[$key] = [
                    'key' => $this->fiedls[$key],
                    'val' => $val == 'Ja' ? 1 : 0
                ];
            }
        }

        $tmp['merges'] = $merges;

        return $tmp;
    }
} 