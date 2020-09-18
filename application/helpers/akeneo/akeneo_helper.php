<?php
require __DIR__.'/../vendor/autoload.php';

class AkeneoApi
{
    /**
    * @var AkeneoPimClientInterface
    */
    protected $client;

    /**
    * @var MockWebServer 
    */
    protected $server;

    protected $projectId;

    protected $scope = null;

    protected $family = null;

    public function __construct($projectId, $access)
    {
        $this->projectId = $projectId;
        $init = $this->createClient($projectId, $access);
        $this->client = $init['client'];
        // $this->server = $init['server'];
        // $this->server->start();
    }

    /**
    * @return AkeneoPimClientInterface
    */
    protected function createClient($projectId, $access)
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $clientId = $access['clientId'];
        $secret   = $access['secret'];
        $username = $access['username'];
        $password = $access['password'];
        $url      = $access['url'];

        $this->scope    = $access['scope'];
        $this->family   = $access['family'];

        // $clientId = '1_4d13qdx728g0wwgkscoos4gsc48s4404wkcssocww4g84wgc0g';
        // $secret   = '4fc4kdmh71q8cs4sc480kg48wg804wo8ow8gkg8gk44wog4wsg';
        // $username = 'admin';
        // $password = 'admin';
        // $url      = 'http://ruijgrok-akeneo.web-company.com/';
        $init     = [];
        if ($clientId && $secret && $username && $password && $url) {
            // $init['server'] = new \donatj\MockWebServer\MockWebServer(8081, $url);
            $clientBuilder  = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder($url);
            $init['client'] = $clientBuilder->buildAuthenticatedByPassword(
                $clientId,
                $secret,
                $username,
                $password
            );

            return $init;
        }

        return false;
    }

    public function getProducts() 
    {
        $api = $this->client->getProductApi();
        $result  = $api->listPerPage(10, true, []);
        $this->getMethods($result);
        //$this->dd($result->getItems());
    }

    public function getProductBySku($sku) 
    {
        try {
            $api     = $this->client->getProductApi();
            $product = $api->get($sku);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $product];
    }

    public function createProduct($product)
    {
        try {
            $api = $this->client->getProductApi();
            $newProduct = $this->newProduct($product);
            $response = $api->create($product['model'], $newProduct);
            if (isset($product['image'])) {
                $this->addImage($product);
            }
        } catch (Exception $e) {
            log_message('debug', 'Create prodcut error');
            log_message('debug', var_export($e->getResponseErrors(), true));
            return ['status' => false, 'data' => $e->getResponseErrors()];
        }

        return ['status' => true, 'data' => $response];
    }

    private function newProduct($data)
    {
        $product = [
            'enabled'    => true,
            'family'     => 'accessories',
            'categories' => [$data['categories_ids']['id']],
            'values'     => [
//                'name' => [
//                    [
//                        'data'   => $data['name'],
                        // 'locale' => 'en_US',
                        // 'scope'  => 'ecommerce',
//                        'locale' => null,
//                        'scope'  => null,
//                    ],
                //],
                'description' => [
                    [
                        'data'   => $data['description'],
                        'locale' => 'en_US',
                        'scope'  => $this->scope
                    ]
                ],
                'price' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            [
                                'amount'   => $data['price'],
                                'currency' => "EUR"
                            ]
                        ]
                    ]
                ],
                // 'quantity' => [
                //     [
                //         'locale' => null,
                //         'scope'  => null,
                //         'data'   => $data['quantity']
                //     ]
                // ]
            ],
        ];
        
        if (is_array($data['name'])) {
            foreach ($data['name'] as $lgCode=>$val) {
                $tmp[] = [
                    'data'   => $val,
                    'locale' => $lgCode,
                    'scope'  => null,
                ];
            }
            $product['values']['name'] = $tmp;
        } else {
            $product['values']['name'] = $tmp;
        }

        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $key=>$value) {
                if (($key == 'meta_title' || $key == 'meta_description') && is_array($value)) {
                    $tmp = [];
                    foreach ($value as $lgCode=>$val) {

                        $tmp[] = [
                            'data'   => $val,
                            'locale' => $lgCode,
                            // 'scope'  => 'ecommerce',
                            'scope'  => null,
                        ];
                    }
                    $product['values'][$key] = $tmp;
                } elseif ($key == 'url_key' && is_array($value)) {
                    $tmp = [];
                    foreach ($value as $lgCode=>$val) {

                        $tmp[] = [
                            'data'   => $val,
                            'locale' => $lgCode,
                            'scope'  => $this->scope,
                            // 'scope'  => null,
                        ];
                    }
                    $product['values'][$key] = $tmp;
                } else {
                    $product['values'][$key] = [
                        [
                            'data'   => $value,
                            // 'locale' => 'en_US',
                            // 'scope'  => 'ecommerce',
                            'locale' => null,
                            'scope'  => null,
                        ]
                    ];
                }
                
            }
        }
        return $product;
    }

    public function addImage($product) 
    {
        try {
            $api = $this->client->getProductMediaFileApi();
            $img = $product['image'];
            $mediaFile = realpath($img['path']);

            $productInfos = [
                'identifier' => $product['model'],
                'attribute'  => 'image',
                'scope'      => null,
                'locale'     => null,
            ];

            $response = $api->create($mediaFile, $productInfos);

            return ['status' => true, 'data' => $response];
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }
    }

    public function updateProduct($product, $updateField)
    {
        try {
            $api = $this->client->getProductApi();
            $productFields = $this->getParameters($updateField, $product);
            $response = $api->upsert($product['model'], $productFields);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $response];
    }

    private function getParameters($updateField, $product) 
    {
        $parameters = [];
        foreach ($updateField as $key => $value) {
            if ($key == 'price') {
                $parameters['values'] = [
                    $key => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                [
                                    'amount'   => $value,
                                    'currency' => "USD"
                                ]
                            ]
                        ]
                    ]
                ];
            } elseif ($key == 'name' && is_array($value)) {
                foreach ($value as $lgCode=>$val) {
                    $tmp[] = [
                        'data'   => $val,
                        'locale' => $lgCode,
                        // 'scope'  => 'ecommerce',
                        'scope'  => null,
                    ];
                }
                $parameters['values'][$key] = $tmp;
            } else {
                $parameters['values'][$key] = [
                    [
                        'locale' => 'en_US',
                        'scope'  => null,
                        'data'   => $value,
                    ]
                ];
            }
        }

        if (isset($product['attributes'])) {
            foreach ($product['attributes'] as $key=>$value) {
                if (($key == 'meta_title' || $key == 'meta_description') && is_array($value)) {
                    $tmp = [];
                    foreach ($value as $lgCode=>$val) {

                        $tmp[] = [
                            'data'   => $val,
                            'locale' => $lgCode,
                            // 'scope'  => 'ecommerce',
                            'scope'  => null,
                        ];
                    }
                    $parameters['values'][$key] = $tmp;
                } elseif ($key == 'url_key' && is_array($value)) {
                    $tmp = [];
                    foreach ($value as $lgCode=>$val) {

                        $tmp[] = [
                            'data'   => $val,
                            'locale' => $lgCode,
                            'scope'  => $this->scope,
                            // 'scope'  => null,
                        ];
                    }
                    $parameters['values'][$key] = $tmp;
                } else {
                    $parameters['values'][$key] = [
                        [
                            'data'   => $value,
                            // 'locale' => 'en_US',
                            // 'scope'  => 'ecommerce',
                            'locale' => null,
                            'scope'  => null,
                        ]
                    ];
                }
                
            }
        }

        return $parameters;
    }
    
    private function getMethods($class) 
    {
        echo "<pre>";
        var_dump(get_class_methods($class));
        echo "</pre>";
    }

    private function dd($data)
    {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    }

    public function test() 
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder('http://ruijgrok-akeneo.web-company.com/');
        $client = $clientBuilder->buildAuthenticatedByPassword('1_3m01h7okkqyo8ccsc08ocs884scgk4s4wok4sg8ssscocscgwo', '62qsg304n5kw0kgw44swo08k8kcwsogco8g44cgww048g84', 'admin', 'admin');
        $product = $client->getProductApi()->get('top');

        echo "<pre>";
        var_dump($product);
    }

    public function getCategory($catName = '')
    {
        try {
            $api = $this->client->getCategoryApi();
            $response  = $api->get($catName);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $response];
    }

    public function createCategory($code, $name)
    {
        $result = [];
        try {
            $api = $this->client->getCategoryApi();
            $catInfo = [
                "parent" => 'master',
                "labels" => [
                    "de_DE" => $name,
                    // "en_US" => $name,
                ]
            ];
            $result['status']  = $api->create($code, $catInfo);
            $result['catCode'] = $code;
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $result];
    }
    
    public function getAttribute($code = '')
    {
        $result = [];
        try {
            $api = $this->client->getAttributeApi();
            $response  = $api->get($code);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $response];
    }

    public function getAttributeOptions($attributeCode = '', $code = '')
    {
        $result = [];
        try {
            $api = $this->client->getAttributeOptionApi();
            $response  = $api->get($attributeCode, $code);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $response];
    }

    public function createAtributeOptions($optionCode = '', $code = '')
    {
        $result = [];
        try {
            $api = $this->client->getAttributeOptionApi();
            $result['status']  = $api->create($code, $optionCode, []);
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getResponseErrors()];
        }
        
        return ['status' => true, 'data' => $result];
    }

    public function createAtribute($data = [])
    {
        $result = [];
        
        try {
            $api = $this->client->getAttributeApi();
            $attribute = [
                "type"   => $data['type'],
                "unique" => false,
                "group"  => $data['group'],
                "labels" => [
                    "de_DE" => $data['labels'],
                ]
            ];

            $result['status']  = $api->create($data['code'], $attribute);
            $result['catCode'] = $data['code'];
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }

        return ['status' => true, 'data' => $result];
    }
    
    public function updateUttribute($data = [])
    {
        $result = [];
        
        try {
            $api = $this->client->getAttributeApi();
            $attribute = [
                "type"   => $data['type'],
                "unique" => true,
                "group"  => $data['group'],
                "labels" => [
                    "de_DE" => $data['labels'],
                ]
            ];

            $result['status']  = $api->create($data['code'], $attribute);
            $result['catCode'] = $data['code'];
        } catch (Exception $e) {
            return ['status' => false, 'data' => $e->getMessage()];
        }
    }
}