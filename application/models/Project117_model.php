<?php
class Project117_model extends CI_Model {

    public $projectId;

    private $attibutes_list = [
        'Barcode'       => 'EAN',
        'Merk'          => 'Brand',
        'Leverancier'   => 'Supplier',
        'Nettogewicht'  => 'Weight',
        'BrutoPrice'    => 'msrp',
        'MetaTitle_NL'  => 'meta_title',
        'MetaTitle_GB'  => 'meta_title',
        'MetaTitle_DE'  => 'meta_title',
        'MetaTitle_FR'  => 'meta_title',
        'MetaTitle_EN'  => 'meta_title',
        'MetaDesc_NL'   => 'meta_description',
        'MetaDesc_GB'   => 'meta_description',
        'MetaDesc_DE'   => 'meta_description',
        'MetaDesc_FR'   => 'meta_description',
        'MetaDesc_EN'   => 'meta_description',
        'NameFR'        => 'name',
        'NameDE'        => 'name',
        'NameEN'        => 'name',

    ];

    function __construct()
    {
        parent::__construct();
        $this->projectId = 117;
    }

    // public function getArticleData($article, $finalArticleData)
    // {
        // $attributesArticleData = [];

        // $this->load->model('Akeneo_model');

        // foreach ($article as $key=>$value) {
            // if (isset($this->attibutes_list[$key])) {
                // // $response = $this->Akeneo_model->getAtribute(strtolower($key));
                // $code   = $this->attibutes_list[$key];
                // $code   = strtolower($code);
                // $code   = str_replace(".", "_", $code);
                // $code   = str_replace(" ", "_", $code);

                // $response = $this->Akeneo_model->getAtribute($code, $this->projectId);

                // if ($response === false) {
                    // $data = [
                        // 'code'   => $code,
                        // 'type'   => "pim_catalog_text",
                        // 'group'  => "product",
                        // 'labels' => $this->attibutes_list[$key],
                    // ];
                    // $result = $this->Akeneo_model->createAtribute($data, $this->projectId);
                    // if ($result !== false) {
                        // $attributesArticleData[$code] = $value;
                    // }
                // } else {
                    // if (strripos($key, 'MetaTitle') !== false || strripos($key, 'MetaDesc') !== false) {
                        // $tmp = explode('_', $key);
                        // if (count($tmp) == 2) {
                            // $locale = $tmp[1];
                            // foreach ($response['labels'] as $lgCode=>$title) {
                                // if (strripos($lgCode, $locale) !== false) {
                                    // $attributesArticleData[$code][$lgCode] = $value; 
                                // }
                            // }
                        // }
                    // } elseif($code == 'weight') {
                        // $attributesArticleData[$code] = [
                            // 'amount' => $value,
                            // 'unit' => $response['default_metric_unit']
                        // ];
                    // } elseif(strripos($response['type'], 'select') !== false) {
                        // $responseOpt = $this->Akeneo_model->getAttributeOptions($code, strtolower($value), $this->projectId);
                        // if ($responseOpt === false) {
                            // if ($this->Akeneo_model->createAtributeOptions(strtolower($value), $code, $this->projectId)) {
                                // $attributesArticleData[$code] = strtolower($value);
                            // }
                        // } else {
                            // $attributesArticleData[$code] = strtolower($value);
                        // }
                    // } elseif(strripos($response['type'], 'text') !== false) {
                        // $attributesArticleData[$code] = $value . '';
                    // } else {
                        // $attributesArticleData[$code] = $value;
                    // }
                // }
            // }
        // }

        // $finalArticleData['attributes'] = $attributesArticleData;

        // return $finalArticleData;
    // }
    public function getArticleData($article, $finalArticleData)
    {
        $attributesArticleData = [];
        $this->load->model('Akeneo_model');

        foreach ($article as $key=>$value) {
            if (isset($this->attibutes_list[$key])) {
                // $response = $this->Akeneo_model->getAtribute(strtolower($key));
                $code   = $this->attibutes_list[$key];
                $code   = strtolower($code);
                $code   = str_replace(".", "_", $code);
                $code   = str_replace(" ", "_", $code);

                $response = $this->Akeneo_model->getAtribute($code, $this->projectId);

                if ($response === false) {
                    $data = [
                        'code'   => $code,
                        'type'   => "pim_catalog_text",
                        'group'  => "product",
                        'labels' => $this->attibutes_list[$key],
                    ];
                    $result = $this->Akeneo_model->createAtribute($data, $this->projectId);
                    if ($result !== false) {
                        $attributesArticleData[$code] = $value;
                    }
                } else {
                    if (strripos($key, 'MetaTitle') !== false || strripos($key, 'MetaDesc') !== false) {
                        $tmp = explode('_', $key);
                        if (count($tmp) == 2) {
                            $locale = $tmp[1];
                            foreach ($response['labels'] as $lgCode=>$title) {
                                if (strripos($lgCode, $locale) !== false) {
                                    $attributesArticleData[$code][$lgCode] = $value; 
                                }
                            }
                        }
                    } elseif($code == 'weight') {
                        $attributesArticleData[$code] = [
                            'amount' => $value,
                            'unit' => $response['default_metric_unit']
                        ];
                    } elseif(strripos($response['type'], 'select') !== false) {
                        $responseOpt = $this->Akeneo_model->getAttributeOptions($code, strtolower($value), $this->projectId);
                        if ($responseOpt === false) {
                            $value =  preg_replace('/[^ \w]/', '_', $value);
                            if ($this->Akeneo_model->createAtributeOptions(strtolower($value), $code, $this->projectId)) {
                                $attributesArticleData[$code] = $value;
                            }
                        } else {
                            $attributesArticleData[$code] = $value;
                        }
                    } elseif(strripos($response['type'], 'text') !== false) {
                        if ($code == 'name') {
                            $attributesArticleData['name']['nl_NL'] = $finalArticleData['name'];
                            foreach ($response['labels'] as $lgCode=>$title) {
                                $tmp = explode('_', $lgCode);
                                if (strripos($key, $tmp[0]) !== false) {
                                    $attributesArticleData['name'][$lgCode] = $value;
                                }
                            }
                        } else {
                            $attributesArticleData[$code] = $value . '';
                        }
                    } else {
                        $attributesArticleData[$code] = $value;
                    }
                }
            }
        }

        $urlKey = $this->setUrlKey($attributesArticleData['name'], $finalArticleData['model']);

        if ($urlKey) {
            $attributesArticleData['url_key'] = $urlKey;
        }

        $finalArticleData['attributes'] = $attributesArticleData;

        if (isset($finalArticleData['attributes']['name'])) {
            $finalArticleData['name'] = $finalArticleData['attributes']['name'];
            unset($finalArticleData['attributes']['name']);
        }

        return $finalArticleData;
    }

    protected function setUrlKey($names, $sku)
    {
        $data = [];

        $response = $this->Akeneo_model->getAtribute('url_key', $this->projectId);

        if ($response === false) return false;

        foreach ($response['labels'] as $lgCode=>$title) {
            if (isset($names[$lgCode])) {
                $name = preg_replace('/[^ \w-()+]/', '', $names[$lgCode]);
                $name = preg_replace('/\s+/', '', $name);
                $sku  = preg_replace('/[^ \w-()+]/', '', $sku);
                $sku  = preg_replace('/\s+/', '', $sku);

                $data[$lgCode] = $name .'-'. $sku;
            }
        }

        if(empty($data)) return false;

        return $data;
    }
}