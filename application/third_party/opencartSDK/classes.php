<?php
class CurlRequest
{
    private $url;
    private $postData = array();
    private $response = '';
    private $responseStatus = '';
    private $handle;
    private $headers = array();
    private $isDebug = 0;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function makePostRequest()
    {
        $this->makeRequest("POST");
    }

    public function makeRequest($method = "GET")
    {
        $this->handle = curl_init($this->url);
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);

        if($this->isDebug){
            $this->printRequestInfo($method);
        }

        if ($method != "GET") {
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode($this->postData));

            if($this->isDebug){
                $this->printRequest();
            }
        }

        $headers = array(
            'Content-Type: application/json',
        );

        if (isset($_SESSION['bearerToken']) && !empty($_SESSION['bearerToken'])) {
            $headers[] ='Authorization: Bearer ' . $_SESSION['bearerToken'];
        }

        $headers = array_merge($headers, $this->headers);

        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        $this->response = curl_exec($this->handle);
        $this->responseStatus = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if($this->isDebug){
            $this->printResponse();
        }

        $this->postData = array();
        $this->headers = array();

        curl_close($this->handle);
    }

    public function makePutRequest()
    {
        $this->makeRequest("PUT");
    }

    public function makeDeleteRequest()
    {
        $this->makeRequest("DELETE");
    }

    public function addHeader($headers)
    {
        $this->headers = $headers;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setData($postData)
    {
        $this->postData = $postData;
    }

    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    public function getResponse()
    {
        return json_decode($this->response, true);
    }

    public function printResponse()
    {
        $response = json_decode($this->response, true);
        echo("<h5>Response status: ".$this->getResponseStatus()."</h5>");
        echo("<h4>Response:</h4>");
        echo("<div style='max-height:400px;overflow:auto'>");
        echo("<pre".(empty($response['success']) ? ' style="background:#fb0000;color:#fff;"' : '').">");
        print_r($response);
        echo("</pre></div><hr>");
    }

    public function printRequest()
    {
        echo("<h4>Request:</h4>");
        echo("<pre>");
        echo json_encode($this->postData, JSON_PRETTY_PRINT);
        echo("</pre>");
    }

    public function printRequestInfo($method)
    {
        echo("<h4>Method: ".$method."</h4>");
        echo("<h4>URL: ".$this->url."</h4>");
    }

    public function getRawResponse()
    {
        return $this->response;
    }
}

class Base
{
    /**
     * @var OpenCartRestApi
     */
    public $restAPI;

    /**
     * @var CurlRequest
     */
    protected $curl;

    public function __construct($restAPI)
    {
        $this->restAPI = $restAPI;
        $this->curl = $restAPI->curl;
    }
}

class Product extends Base
{

    public function getProducts($page=1, $limit=10)
    {
        $this->curl->setUrl($this->restAPI->getUrl('rest/product_admin/products&limit='.$limit.'&page='.$page));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }

    public function getProductById($id)
    {

        if (empty($id)) {
            throw new Exception("Product ID cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/product_admin/products&id=' . $id));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }


    public function updateProductQuantity($id, $quantity)
    {
        if (empty($id) || empty($quantity)) {
            throw new Exception('Product ID and quantity cannot be empty for product update');
        }

        $postData = array(
            'quantity' => $quantity
        );

        $this->curl->setUrl($this->restAPI->getUrl('rest/product_admin/products&id=' . $id));
        $this->curl->setData($postData);
        $this->curl->makePutRequest();
        return $this->curl->getResponse();
    }

    public function deleteProductById($id)
    {

        if (empty($id)) {
            throw new Exception("Product ID cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/product_admin/products&id=' . $id));
        $this->curl->makeDeleteRequest();
        return $this->curl->getResponse();
    }

    public function getProductBySku($sku)
    {
        if (empty($sku)) {
            throw new Exception("Product search sku cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/product_admin/getproductbysku&sku=' . $sku));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }
}


class Category extends Base
{

    public function addCategory($category)
    {
        $this->curl->setUrl($this->restAPI->getUrl('rest/category_admin/category'));
        $this->curl->setData($category);
        $this->curl->makePostRequest();
        return $this->curl->getResponse();
    }

    public function getCategories()
    {
        $this->curl->setUrl($this->restAPI->getUrl('rest/category_admin/category'));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }

    public function getCategoryById($id)
    {

        if (empty($id)) {
            throw new Exception("Category ID cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/category_admin/category&id=' . $id));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }

    public function deleteCategoriesById($categories)
    {

        if (empty($categories)) {
            throw new Exception("Category IDs cannot be empty");
        }
        $this->curl->setData($categories);
        $this->curl->setUrl($this->restAPI->getUrl('rest/category_admin/category'));
        $this->curl->makeDeleteRequest();
        return $this->curl->getResponse();
    }
}


class Order extends Base
{

    public function getOrders()
    {
        $this->curl->setUrl($this->restAPI->getUrl('rest/order_admin/orders'));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }

    public function getOrderById($id)
    {

        if (empty($id)) {
            throw new Exception("Order ID cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/order_admin/orders&id=' . $id));
        $this->curl->makeRequest();
        return $this->curl->getResponse();
    }
}

class Customer extends Base
{

    public function getToken($basicToken)
    {
        $this->curl->setUrl($this->restAPI->getUrl('rest/admin_security/gettoken&grant_type=client_credentials'));
        $this->curl->addHeader(array('Authorization: Basic '.$basicToken));
        $this->curl->makePostRequest();
        return $this->curl->getResponse();
    }

    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            throw new Exception("Username and password cannot be empty");
        }

        $this->curl->setUrl($this->restAPI->getUrl('rest/admin_security/login'));
        $this->curl->setData(array(
            'username' => $username,
            'password' => $password
        ));

        $this->curl->makePostRequest();

        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return true;
        }
        return false;
    }

    public function logout()
    {

        $this->curl->setUrl($this->restAPI->getUrl('rest/admin_security/logout'));
        $this->curl->makePostRequest();
        return $this->curl->getResponse();
    }
}

class OpenCartRestApi
{
    /**
     * @var CurlRequest
     */
    public $curl;

    /**
     * @var Customer
     */
    public $customer;
    /**
     * @var Product
     */
    public $product;
    /**
     * @var Category
     */
    public $category;
    /**
     * @var Order
     */
    public $order;

    private $url;

    public function __construct($url)
    {
        $this->url = rtrim('http://' . preg_replace('/^https?\:\/\//', '', $url), '/') . '/index.php?route=';

        $this->curl = new CurlRequest($url);
        $this->customer = new Customer($this);
        $this->product = new Product($this);
        $this->category = new Category($this);
        $this->order = new Order($this);
    }

    public function getUrl($method)
    {
        return $this->url . $method;
    }

    public function printServiceTitle($title)
    {
        echo("<h3>".$title."</h3>");
    }
}
