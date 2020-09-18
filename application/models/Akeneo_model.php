<?php
class Akeneo_model extends CI_Model {

    protected $akeneo;

    protected $projectId;

    private $compareProductField = [
        'identifier'  => 'model',
        'name'        => 'name',
        'description' => 'description',
        'price'       => 'price',
        'quantity'    => 'quantity',
        'image'       => 'image',
        'ean'         => 'ean',
    ];

    function __construct()
    {
        parent::__construct();
        $this->load->helpers('akeneo/Akeneo');
        $this->load->model('Projects_model');
    }

    private function init($projectId) {
        $this->load->model('Projects_model');
        $this->projectId = $projectId;

        $access['clientId'] = $this->Projects_model->getValue('akeneo_client_id', $projectId);
        $access['secret']   = $this->Projects_model->getValue('akeneo_client_secret', $projectId);
        $access['username'] = $this->Projects_model->getValue('akeneo_login', $projectId);
        $access['password'] = $this->Projects_model->getValue('akeneo_password', $projectId);
        $access['url']      = $this->Projects_model->getValue('akeneo_url', $projectId);
        $access['scope']    = 'ecommerce';//$this->Projects_model->getValue('akeneo_scope', $projectId);
        $access['family']   = $this->Projects_model->getValue('akeneo_family', $projectId);

        return new AkeneoApi($projectId, $access);
    }
    public function updateArticles($projectId, $articles) {
        $i = 0;
        $this->akeneo = $this->init($projectId);
        foreach($articles as $article){
            $productExists = $this->checkProductExists($article, $projectId);
            if($productExists){
                $this->updateProduct($article, $productExists);
            } else {
                $this->createProduct($article);
            }
        }
    }

    public function checkProductExists($article)
    {   
        $response = $this->akeneo->getProductBySku($article['model']);

        if ($response['status'] === false) {
            return false;
        }

        return $response['data'];
    }

    public function createProduct($article)
    {
        $response = $this->akeneo->createProduct($article);
        if ($response['status'] === false) {
            log_message('debug', 'Could not create product '. $article['model'].'. Result: '.print_r($article, true));
            api2cart_log($this->projectId, 'importarticles', 'Could not create product '. $article['model'].'. Result: '.print_r($response['data'], true));
        } else {
            api2cart_log($this->projectId, 'importarticles', 'Created product '. $article['model']);
        }
    }

    public function updateProduct($article, $productExists)
    {
        $updateField = [];
        $imgChecker  = false;
        if (!isset($productExists['values']['name']) && isset($article['name'])) {
            $productExists['values']['name'] = 'test';
        }

        var_dump($productExists['values']);
        foreach ($productExists['values'] as $key => $data) {
            $value = $this->getValue($data, $key);
            if (!empty($value)) {
                if ($key == 'image' && isset($article['image'])) {
                    $imgChecker = true;
                    $imgName = isset($article['image']) ? $article['image']['image_name'] : '';
                    if (!strpos($value, $imgName)) {
                        $this->akeneo->addImage($article);
                    }
                } elseif ($value != $article[$this->compareProductField[$key]]) {
                    $updateField[$key] = $article[$this->compareProductField[$key]];
                }
            }
        }

        if ($key == 'name') {
            if ($value != $article[$this->compareProductField[$key]]) {
                $updateField[$key] = $article[$this->compareProductField[$key]];
            }
        }

        if ($imgChecker === false && isset($article['image'])) {
            $response = $this->akeneo->addImage($article);
            if ($response['status'] === false) {
                api2cart_log($this->projectId, 'importarticles', 'Could not updated product image'. $article['model'] . '. Result: ' . print_r($response['data'], true));
            } else {
                api2cart_log($this->projectId, 'importarticles', 'Updated product image' . $article['model']);
            }
        }

        if (count($updateField) || isset($article['attributes'])) {
            $response = $this->akeneo->updateProduct($article, $updateField);
            if ($response['status'] === false) {
                api2cart_log($this->projectId, 'importarticles', 'Could not create product '. $article['model'] . '. Result: ' . print_r($response['data'], true));
            } else {
                api2cart_log($this->projectId, 'importarticles', 'Updated product ' . $article['model']);
            }
        }
    }

    public function findCategory($projectId, $categoryCode)
    {
        $cat = [];
        $this->akeneo = $this->init($projectId);
        $response = $this->akeneo->getCategory($categoryCode);
        // var_dump($this->akeneo, $categoryCode);exit;
        if ($response['status'] === false) {
            return false;
        }

        return $cat['items'][0] = ['id' => $response['data']['code']];
    }

    public function createCategory($projectId, $code, $name)
    {
        $this->akeneo = $this->init($projectId);
        $response = $this->akeneo->createCategory($code, $name);

        if ($response['status'] === false) {
            api2cart_log($projectId, 'importarticles', 'Could not create category '. $name .'. Result: '.print_r($response['data'], true));
            return false;
        } else {
            api2cart_log($projectId, 'importarticles', 'Created category '. $name);
        }

        return $response['data']['catCode'];
    }

    protected function getValue($data, $key)
    {
        switch ($key) {
            case 'price':
            $result = $data[0]['data'][0]['amount'];
            break;
            default:
            $result = $data[0]['data'];
        }

        return $result;
    }
    
    public function getAtribute($code, $projectId = '')
    {
        if (empty($this->akeneo)) {
            $this->akeneo = $this->init($projectId);
        }
        
        $response = $this->akeneo->getAttribute($code);

        if ($response['status'] === false) {
            return false;
        }

        return $response['data'];
    }

    
    public function getAttributeOptions($attributeCode, $code, $projectId)
    {
        if (empty($this->akeneo)) {
            $this->akeneo = $this->init($projectId);
        }
        
        $response = $this->akeneo->getAttributeOptions($attributeCode, $code);

        if ($response['status'] === false) {
            return false;
        }

        return $response['data'];
    }

    
    public function createAtributeOptions($attributeCode, $code, $projectId)
    {
        if (empty($this->akeneo)) {
            $this->akeneo = $this->init($projectId);
        }

        $response = $this->akeneo->createAtributeOptions($attributeCode, $code);

        if ($response['status'] === false) {
            api2cart_log($projectId, 'importarticles', 'Could not create option '.$attributeCode.' for attribute '. $code .'. Result: '.print_r($response['data'], true));
            return false;
        } else {
            api2cart_log($projectId, 'importarticles', 'Created option '.$attributeCode.' for attribute '. $code);
        }

        return $response['status'];
    }

    public function createAtribute($data, $projectId)
    {   
        $response = $this->akeneo->createAtribute($data);

        if ($response['status'] === false) {
            api2cart_log($projectId, 'importarticles', 'Could not create attribute '. $data['code'] .'. Result: '.print_r($response['data'], true));
            return false;
        } else {
            api2cart_log($projectId, 'importarticles', 'Created attribute '. $data['code']);
        }

        return $response['data'];
    }

    public function dd($data)
    {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }

}