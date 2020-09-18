<?php
require __DIR__.'/../vendor/autoload.php';


/**
 * PrestashopApi class
 * Helper for working with prestashop API sdk
 */
class PrestashopApi
{
    /** @var array array of instance of the PrestashopApi class */
    private static $instances = [];
    
    /** @var object instance of the PrestaShopWebservice class */
    private $client;

    /** @var string preshtashop uri */
    protected $shop_uri;

    /** @var string preshtashop api key */
    protected $api_key;

    /**
     * Create a new PrestashopApi helper instance
     * @param string $shop_uri
     * @param string $api_key
     * @param boolean $debug
     */ 
    protected function __construct($shop_uri, $api_key, $debug = FALSE)
    {
        if (!$shop_uri || !$api_key) return false;
        $this->shop_uri = $shop_uri;
        $this->api_key  = $api_key;
        $this->client   = new PrestaShopWebservice($shop_uri, $api_key, $debug);
    }   

    protected function __clone() { }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * This is the static method that controls the access to the PrestashopApi
     * instance.
     * @param string $shop_uri
     * @param string $api_key
     * @param boolean $debug
     * @return void
     */
    public static function getInstance($shop_uri, $api_key, $debug = FALSE)
    {
        $cls = static::class;
        if (!isset(static::$instances[$cls])) {
            static::$instances[$cls] = new static($shop_uri, $api_key, $debug = FALSE);
        }

        return static::$instances[$cls];
    }

    public function test_conncet() 
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $url = 'http://presta.loc';
        $key  = 'JJYF1IQNI1PTYKBABPWEDIL7YKM1YWMM';
        $debug = false;
        
        $webService = new PrestaShopWebservice($url, $key, $debug);
        $xml = $webService->get(array('url' => $url.'/api/products?schema=blank'));
        $resource_product = $xml->children()->children();
        unset($resource_product->id);
        unset($resource_product->position_in_category);
        unset($resource_product->manufacturer_name);
        unset($resource_product->id_default_combination);
        unset($resource_product->associations);

        $resource_product->id_shop = 1;
        $resource_product->minimal_quantity = 1;
        $resource_product->available_for_order = 1;
        $resource_product->show_price = 1;
        //$resource_product->quantity = 10;           // la cantidad hay que setearla por medio de un webservice particular
        $resource_product->id_category_default = 2;   // PRODUCTOS COMO CATEGORÍA RAIZ
        $resource_product->price = 12.23;
        $resource_product->active = 1;
        $resource_product->visibility = 'both';
        $resource_product->name->language[0] = "blablabla";
        $resource_product->description->language[0] = "blablabla";
        $resource_product->state = 1;

        $opt = array('resource' => 'products');
        $opt['postXml'] = $xml->asXML();
        $xml = $webService->add($opt);
        echo "<pre>";
        var_export($xml);exit;
        
    }

    /**
     * Get orders id from prestashop 
     * @param int $offset
     * @param int $amount
     * @return void
     */
    public function get_orders($offset, $amount)
    {
        try {
            $xml = $this->client->get(['resource' => 'orders', 'limit' => $offset.','.$amount, 'sort' => '[id_DESC]']);
            $responce = $xml->orders->children();
            $ordersId = $this->xml2array($responce);

            $result['status'] = true;
            $result['data']   = array_column($ordersId, '@attributes');
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get order info from prestashop
     * @param string $orderId
     * @return array
     */
    public function get_order($orderId)
    {
        try {
            $xml = $this->client->get(['resource' => 'orders', 'id' => $orderId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->order;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get customer info from prestashop by customer id
     * @param string $customerId
     * @return array
     */
    public function get_customer($customerId)
    {
        try {
            $xml = $this->client->get(['resource' => 'customers', 'id' => $customerId]);

            $result['status'] = true;
            $result['data']   = (array) $xml->customer;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get currency info from prestashop by currency id
     * @param string $currencyId
     * @return array
     */
    public function get_currencies($currencyId)
    {
        try {
            $xml = $this->client->get(['resource' => 'currencies', 'id' => $currencyId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->currency;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get addresses info from prestashop by addresses id
     * @param string $addressesId
     * @return array
     */
    public function get_addresses($addressesId)
    {
        try {
            $xml = $this->client->get(['resource' => 'addresses', 'id' => $addressesId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->address;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get country info from prestashop by country id
     * @param string $countryId
     * @return array
     */
    public function get_countries($countryId)
    {
        try {
            $xml = $this->client->get(['resource' => 'countries', 'id' => $countryId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->country;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get carriers info from prestashop by carrier id
     * @param string $countryId
     * @return array
     */
    public function get_carriers($carrierId)
    {
        try {
            $xml = $this->client->get(['resource' => 'carriers', 'id' => $carrierId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->carrier;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get order info about products in order 
     * @param string $orderId
     * @return array
     */
    public function get_order_details($orderId)
    {
        try {
            $xml = $this->client->get(['resource' => 'order_details', 'id' => $orderId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->order_detail;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get order status
     * @param string $stateId
     * @return array
     */
    public function get_order_states($stateId)
    {
        try {
            $xml = $this->client->get(['resource' => 'order_states', 'id' => $stateId]);
            $result['status'] = true;
            $result['data']   = (array) $xml->order_state->name;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get product by sku
     * @param string $sku
     * @return array
     */
    public function get_products($sku)
    {
        try {
            $xml       = $this->client->get(['resource' => 'products', 'filter[reference]' => '['.$sku.']']);
            $responce  = $xml->products;
            $product   = $responce->product;
            if (count((array) $product)) {
                $productId = (array) $product->attributes()->id; 
                $product   = $this->get_product($productId[0]);
                $product   = $product['status'] ? $product['data'] : false;
                ;
            } else {
                $product = false;
            }

            $result['status'] = true;
            $result['data']   = $product;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }
        return $result;
    }

    /**
     * Get product by id
     * @param string $id
     * @return array
     */
    public function get_product($id)
    {
        try {
            $xml = $this->client->get(['resource' => 'products', 'id' => $id]);
            $result['status'] = true;
            $result['data']   = (array) $xml->product;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }
        return $result;
    }

    /**
     * Get product shema
     * @return xml
     */
    public function get_product_shema()
    {
        try {
            $xml = $this->client->get(['resource' => 'products?schema=blank']);
            $result['status'] = true;
            $result['data']   = $xml;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }
        return $result;
    }

    /**
     * Create product in prestashop
     * @param array $article
     * @return array
     */
    public function create_product($article) 
    {   
        $xml = $this->client->get(['resource' => 'products?schema=blank']);
        // $xml = $this->client->get(['resource' => 'products']);
        
        if (!isset($xml->product)) {
            return false;
        }

        $resource_product = $xml->children()->children();

        unset($resource_product->id);
        unset($resource_product->position_in_category);
        unset($resource_product->manufacturer_name);
        unset($resource_product->id_default_combination);
        unset($resource_product->associations);
        unset($resource_product->associations->categories); 

        $resource_product->id_shop                  = 1;
        $resource_product->minimal_quantity         = 1;
        $resource_product->available_for_order      = 1;
        $resource_product->show_price               = 1;
        $resource_product->id_category_default      = 2;//isset($article['categories_ids']) ? $article['categories_ids'] : 1;
        $resource_product->price                    = isset($article['price']) ? $article['price'] : 0;
        $resource_product->active                   = 1;
        $resource_product->visibility               = 'both';
        $resource_product->name->language[0]        = $article['name'];
        $resource_product->description->language[0] = $article['description'];
        $resource_product->state                    = 1;
        $resource_product->reference                = $article['model'];


        $categories = $resource_product->associations->addChild('categories'); 
        $category = $categories->addChild('category');
        $category->addChild('id', 2);
        $category = $categories->addChild('category');
        $category->addChild('id', isset($article['categories_ids']) ? $article['categories_ids'] : 1);

        try {
            $xml = $this->client->add(['resource' => 'products', 'postXml' => $xml->asXML()]);
            $result['status'] = true;
            if (isset($xml->product->id)) {
                if (isset($article['quantity'])) {
                    $stock = $this->set_stock($xml->children()->children(), $article);
                    $result['stock'] = $stock['status'] ? true : $stock['data'];
                }
                if (isset($article['image'])) {
                    $image = $this->add_image((array)$xml->product->id, $article);
                }
            }
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * add image for rpoduct
     *
     * @param object $productId
     * @param array $image image info from prestashop
     * @param array $article 
     * @return array
     */
    public function add_image($productId, $article)
    {
        foreach($article as $key=>$data) {
            if (stripos($key, 'image') !== FALSE) {
                $image     = $article[$key];
                $url       = $this->shop_uri;
                $key       = $this->api_key;
                $productId = $productId[0];
                $urlImage  = $url.'/api/images/products/'.$productId.'/';
                
                $args['image'] = new CurlFile($image['path'], $image['image_name'], null);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
                curl_setopt($ch, CURLOPT_URL, $urlImage);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERPWD, $key.':');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (200 != $httpCode) {
                    log_message('error', 'image not added fro product ' . $productId . ' ' . var_export($result, true));
                }
            }
        }
        
    }

    /**
     * Set stock quantity
     *
     * @param object $product Product XML after adding product to prestashop
     * @param array $article 
     * @return array
     */
    public function set_stock($product, $article)
    {
        if (is_object($product)) {
            $associations       = $product->associations;
            $stockId            = (array) $associations->stock_availables->stock_available->id;
            $idProductAttribute = (array) $associations->stock_availables->stock_available->id_product_attribute;
            $id                 = $product->id;
        } else {
            $associations       = $product['associations'];
            $stockId            = (array) $associations->stock_availables->stock_available->id;
            $idProductAttribute = (array) $associations->stock_availables->stock_available->id_product_attribute;
            $id                 = $product['id'];
        }

        $xml       = $this->client->get(['resource' => 'stock_availables?schema=blank']);
        $resources = $xml->children()->children();

        $resources->id                   = $stockId[0];
        $resources->id_product           = $id;
        $resources->quantity             = $article['quantity'];
        $resources->id_shop              = 1;
        $resources->out_of_stock         = 1;
        $resources->depends_on_stock     = 0;
        $resources->id_product_attribute = $idProductAttribute[0];

        try {
            $xml = $this->client->edit(['resource' => 'stock_availables', 'id'=> $stockId[0], 'putXml' => $xml->asXML()]);
            $result['status'] = true;
            $result['data']   = $xml;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Update product
     *
     * @param array $article
     * @return array
     */
    public function update_product($article, $productExists)
    {
        $poductID = $productExists['id'];
        // $xml      = $this->client->get(['resource' => 'products?schema=blank']);
        $xml = $this->client->get(['resource' => 'products', 'id' => 156]);
        echo "<pre>";
        var_export($xml);exit;
        if (!isset($xml->product)) {
            return false;
        }
        $resource_product = $xml->children()->children();

        unset($resource_product->position_in_category);
        unset($resource_product->manufacturer_name);
        unset($resource_product->id_default_combination);
        unset($resource_product->associations);

        $resource_product->id                       = $poductID;
        $resource_product->id_shop                  = 1;
        $resource_product->minimal_quantity         = 1;
        $resource_product->available_for_order      = 1;
        $resource_product->show_price               = 1;
        $resource_product->id_category_default      = 9;
        $resource_product->price                    = isset($article['price']) ? $article['price'] : 0;
        $resource_product->active                   = 1;
        $resource_product->visibility               = 'both';
        $resource_product->name->language[0]        = $article['name'];
        $resource_product->description->language[0] = $article['description'];
        $resource_product->state                    = 1;
        $resource_product->reference                = $article['model'];

        try {
            $xml = $this->client->edit(['resource' => 'products', 'id' => $poductID, 'putXml' => $xml->asXML()]);
            $result['status'] = true;
            // if (isset($xml->product->id) && isset($article['quantity'])) {
            //     $stock = $this->set_stock($xml->children()->children(), $article);
            //     $result['stock'] = $stock['status'] ? true : $stock['data'];
            // }
            if (isset($article['quantity'])) {
                $stock = $this->set_stock($xml->children()->children(), $article);
                $result['stock'] = $stock['status'] ? true : $stock['data'];
            }
            // if (isset($article['image'])) {
            //     $image = $this->add_image((array) $xml->product->id, $article['image']);
            // }
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get category by name
     * @param string $name category name
     * @return array
     */
    public function get_categories($name)
    {
        try {
            $xml        = $this->client->get(['resource' => 'categories', 'filter[name]' => '['.$name.']']);
            $categories = $xml->categories;
            $categories = isset($categories->category) ? (array) $categories->category : false;
            
            $result['status'] = true;
            $result['data']   = $categories{'@attributes'}['id'];
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }
        return $result;
    }

    /**
     * Get category by name
     * @param string $name category name
     * @return array
     */
    public function create_category($name)
    {
        $xml       = $this->client->get(['resource' => 'categories?schema=blank']);
        // $xml       = $this->client->get(['resource' => 'categories', 'id' => 1]);
        $resources = $xml -> children()->children();
        unset($resources->id);
        unset($resources->position);
        unset($resources->date_add);
        unset($resources->date_upd);
        unset($resources->level_depth);
        unset($resources->nb_products_recursive);
        // unset($resources->id_parent);
        $cat_name = preg_replace('/[^ a-zA-Z\d]/ui', '',$name);
        $cat_name = strtolower(str_replace(' ', '_', $name));

        $resources->id_parent = '2';
        $resources->name ->language[0][0] = $name;
        $resources->link_rewrite->language[0][0] = $cat_name;
        $resources->active = 1;
        $resources->id_shop_default = 1;
        $resources->is_root_category = 0;

        try {
            $xml = $this->client->add(['resource' => 'categories', 'postXml' => $xml->asXML()]);
            $result['status'] = true;
            $result['data']   = (array) $xml->category->id;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Create customer 
     * @param array $customer array with customer data
     * @return array
     */
    public function create_customer($customer)
    {
        $xml       = $this->client->get(['resource' => 'customers?schema=blank']);
        // $resources = $xml -> children()->children();
        // Adding dinamic values
        // Required
        $xml->customer->lastname            = $customer['last_name'];
        $xml->customer->firstname           = $customer['first_name'];
        $xml->customer->email               = $customer['email'];
        // Others
        $xml->customer->id_lang             = 1;
        $xml->customer->id_shop             = 1;
        $xml->customer->id_shop_group       = 1;
        $xml->customer->id_default_group    = 3; // Customers
        $xml->customer->active              = 1; 
        $xml->customer->newsletter          = 1;

        $xml->customer->associations->groups->group[0]->id = 3; // customers

        try {
            $xml = $this->client->add(['resource' => 'customers', 'postXml' => $xml->asXML()]);
            $id_customer = (array) $xml->customer->id;
            $result['status'] = true;
            if ($id_customer[0]) {
                $addres = $this->set_adress($id_customer[0], $customer);
                $result['addres'] = $addres['status'] ? true : $addres['data'];
            }
            // $result['data']   = (array) $xml->category->id;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }
    }

    /**
     * Add adress
     * @param string $is_customer
     * @param array $customer array with customer data
     * @return array
     */
    public function set_adress($id_customer, $customer)
    {
        $xml = $this->client->get(['resource' => 'addresses', 'id'=> 1]);

        $xml->address->id_customer  = (int) $id_customer;
        $xml->address->id_country   = 8;//$customer['address_book_country_1'];
        $xml->address->alias        = $customer['address_book_first_name_1'].' '.$customer['address_book_last_name_1'].'\'alias';
        $xml->address->lastname     = $customer['last_name'];
        $xml->address->firstname    = $customer['first_name'];
        $xml->address->city         = $customer['address_book_city_1'];
        $xml->address->address1     = $customer['address_book_address1_1'];
        // Others
        $xml->address->phone_mobile = $customer['phone'];
        $xml->address->postcode     = $customer['address_book_postcode_1'];


        try {
            $xml = $this->client->add(['resource' => 'addresses', 'postXml' => $xml->asXML()]);

            $result['status'] = true;
            // $result['data']   = (array) $xml->category->id;
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * Convert xml to array
     * @param object $xmlObject
     * @param array $out
     * @return array
     */
    public function xml2array ( $xmlObject, $out = array () )
    {   
        $xmlArray = (array) $xmlObject;
        $xmlArray = isset($xmlArray['order']) ? $xmlArray['order'] : $xmlArray;

        foreach ($xmlArray as $index => $node) {
            $out[$index] = (is_object ( $node )) ? $this->xml2array ( $node ) : $node;
        }

        return $out;
    }

}