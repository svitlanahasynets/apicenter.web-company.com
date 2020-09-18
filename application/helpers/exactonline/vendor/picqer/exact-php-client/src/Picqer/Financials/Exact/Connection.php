<?php namespace Picqer\Financials\Exact;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;

/**
 * Class Connection
 *
 * @package Picqer\Financials\Exact
 *
 */
class Connection
{
    /**
     * @var string
     */
    private $baseUrl = 'https://start.exactonline.nl';

    /**
     * @var string
     */
    private $apiUrl = '/api/v1';

    /**
     * @var string
     */
    private $authUrl = '/api/oauth2/auth';

    /**
     * @var string
     */
    private $tokenUrl = '/api/oauth2/token';

    /**
     * @var
     */
    private $exactClientId;

    /**
     * @var
     */
    private $exactClientSecret;

    /**
     * @var
     */
    public $authorizationCode;

    /**
     * @var
     */
    public $accessToken;

    /**
     * @var
     */
    public $tokenExpires;

    /**
     * @var
     */
    public $refreshToken;

    /**
     * @var
     */
    private $redirectUrl;

    /**
     * @var
     */
    private $division;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var callable(Connection)
     */
    private $tokenUpdateCallback;

    /**
     * @var
     */
    private $projectId;

    /**
     *
     */
    protected $middleWares = [];


    /**
    * @var
    */
    public $nextUrl = null;

    /**
     * @return Client
     */
    private function client()
    {
        if ($this->client) {
            return $this->client;
        }

        $handlerStack = HandlerStack::create();
        foreach ($this->middleWares as $middleWare) {
            $handlerStack->push($middleWare);
        }

        $this->client = new Client([
            'http_errors' => true,
            'handler' => $handlerStack,
            'expect' => false,
        ]);

        return $this->client;
    }

    public function insertMiddleWare($middleWare)
    {
        $this->middleWares[] = $middleWare;
    }

    public function connect()
    {
        // Redirect for authorization if needed (no access token or refresh token given)
        if ($this->needsAuthentication()) {
            $this->redirectForAuthorization();
        }

        // If access token is not set or token has expired, acquire new token
        if (empty($this->accessToken) || $this->tokenHasExpired()) {
            $this->acquireAccessToken();
        }

        $client = $this->client();

        return $client;
    }

    /**
     * @param string $method
     * @param $endpoint
     * @param null $body
     * @param array $params
     * @param array $headers
     * @return Request
     */
    private function createRequest($method = 'GET', $endpoint, $body = null, array $params = [], array $headers = [])
    {
        // Add default json headers to the request
        $headers = array_merge($headers, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ]);

        // If access token is not set or token has expired, acquire new token
        if (empty($this->accessToken) || $this->tokenHasExpired()) {
            $this->acquireAccessToken();
        }

        // If we have a token, sign the request
        if (!$this->needsAuthentication() && !empty($this->accessToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        // Create param string
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }

        // Create the request
        $request = new Request($method, $endpoint, $headers, $body);

        return $request;
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws ApiException
     */
    public function get($url, array $params = [])
    {
        //log_message('debug', 'Connect Url  = ' . $url);
        $url = $this->formatUrl($url, $url !== 'current/Me', $url == $this->nextUrl);
        //log_message('debug', 'Connect Url after format = ' . $url);
        try {
            $request = $this->createRequest('GET', $url, null, $params);
            $response = $this->client()->send($request);

            return $this->parseResponse($response, $url != $this->nextUrl);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }


    public function getImage($url, array $params = [])
    {

        try {
            $request = $this->createRequest('GET', $url, null, $params);
            $response = $this->client()->send($request);

            return $response->getBody()->getContents();
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param $url
     * @param $body
     * @return mixed
     * @throws ApiException
     */
    public function post($url, $body)
    {
        $url = $this->formatUrl($url);
        //log_message('debug', 'Post url = ' . $url);
        try {
            $request  = $this->createRequest('POST', $url, $body);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param $url
     * @param $body
     * @return mixed
     * @throws ApiException
     */
    public function put($url, $body)
    {
        $url = $this->formatUrl($url);

        try {
            $request  = $this->createRequest('PUT', $url, $body);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @param $url
     * @return mixed
     * @throws ApiException
     */
    public function delete($url)
    {
        $url = $this->formatUrl($url);

        try {
            $request  = $this->createRequest('DELETE', $url);
            $response = $this->client()->send($request);

            return $this->parseResponse($response);
        } catch (Exception $e) {
            $this->parseExceptionForErrorMessages($e);
        }
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->baseUrl . $this->authUrl . '?' . http_build_query(array(
            'client_id' => $this->exactClientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'force_login' => 1
        ));
    }

    /**
     * @param mixed $exactClientId
     */
    public function setExactClientId($exactClientId)
    {
        $this->exactClientId = $exactClientId;
    }

    /**
     * @param mixed $exactClientSecret
     */
    public function setExactClientSecret($exactClientSecret)
    {
        $this->exactClientSecret = $exactClientSecret;
    }

    /**
     * @param mixed $authorizationCode
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param mixed $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }


    /**
     *
     */
    public function redirectForAuthorization()
    {
        $authUrl = $this->getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return bool
     */
    public function needsAuthentication()
    {
        return empty($this->refreshToken) && empty($this->authorizationCode);
    }

    /**
     * @param Response $response
     * @param bool $returnSingleIfPossible
     * @return mixed
     * @throws ApiException
     */
    private function parseResponse(Response $response, $returnSingleIfPossible = true)
    {
        try {

            if ($response->getStatusCode() === 204) {
                return [];
            }

            Psr7\rewind_body($response);
            $json = json_decode($response->getBody()->getContents(), true);
            if (array_key_exists('d', $json)) {
                if (array_key_exists('__next', $json['d'])) {
                    $this->nextUrl = $json['d']['__next'];
                }
                else {
                    $this->nextUrl = null;
                }

                if (array_key_exists('results', $json['d'])) {
                    if ($returnSingleIfPossible && count($json['d']['results']) == 1) {
                        return $json['d']['results'][0];
                    }

                    return $json['d']['results'];
                }

                return $json['d'];
            }

            return $json;
        } catch (\RuntimeException $e) {
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function getCurrentDivisionNumber()
    {
        if (empty($this->division) ||  $this->projectId == 68) {
            $me             = new Me($this);
            $this->division = $me->find()->CurrentDivision;
        }

        return $this->division;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    private function acquireAccessToken()
    {
        		// Create a new account
        // If refresh token not yet acquired, do token request


        //log_message('debug', 'get params ' . var_export($_GET, true));
        if (empty($this->refreshToken) || isset($_GET['code'])) {
            $body = [
                'form_params' => [
                    'redirect_uri' => $this->redirectUrl,
                    'grant_type' => 'authorization_code',
                    'client_id' => $this->exactClientId,
                    'client_secret' => $this->exactClientSecret,
                    'code' => $this->authorizationCode
                ]
            ];
        } else { // else do refresh token request
            $body = [
                'form_params' => [
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->exactClientId,
                    'client_secret' => $this->exactClientSecret,
                ]
            ];
        }

		//if ($this->projectId == 54) {
			//log_message('debug', 'Customer body ' . var_export($body, true));
		//}
        $response = $this->client()->post($this->getTokenUrl(), $body);

		//if ($this->projectId == 54) {
			//log_message('debug', 'Customer response ' . var_export($response, true));
		//}
        if ($response->getStatusCode() == 200) {
            Psr7\rewind_body($response);
            $body = json_decode($response->getBody()->getContents(), true);
            //log_message('debug', 'Customer response $body ' . var_export($body, true));
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->accessToken  = $body['access_token'];
                $this->refreshToken = $body['refresh_token'];
                $this->tokenExpires = $this->getDateTimeFromExpires($body['expires_in']);

				// Edited by Laurens Schuitemaker
				$instance = get_instance();
				$instance->load->model('Exactonline_model');
			    $instance->Exactonline_model->setValue('exact_accesstoken', $this->accessToken);
			    $instance->Exactonline_model->setValue('exact_refreshtoken', $this->refreshToken);
			    $instance->Exactonline_model->setValue('exact_expires_in', $this->tokenExpires);

                if (is_callable($this->tokenUpdateCallback)) {
                    call_user_func($this->tokenUpdateCallback, $this);
                }
            } else {
                throw new ApiException('Could not acquire tokens, json decode failed. Got response: ' . $response->getBody()->getContents());
            }
        } else {
            throw new ApiException('Could not acquire or refresh tokens');
        }
    }


    private function getDateTimeFromExpires($expires)
    {
        if (!is_numeric($expires)) {
            throw new \InvalidArgumentException('Function requires a numeric expires value');
        }

        return time() + 600;
    }

    /**
     * @return mixed
     */
    public function getTokenExpires()
    {
        return $this->tokenExpires;
    }

    /**
     * @param mixed $tokenExpires
     */
    public function setTokenExpires($tokenExpires)
    {
        $this->tokenExpires = $tokenExpires;
    }

    private function tokenHasExpired()
    {
        if (empty($this->tokenExpires)) {
            return true;
        }

        return $this->tokenExpires <= time() + 30;
    }

    private function formatUrl($endPoint, $includeDivision = true, $formatNextUrl = false)
    {
        if ($formatNextUrl) {
            return $endPoint;
        }

        if ($includeDivision) {
            return implode('/', [
                $this->getApiUrl(),
                $this->getCurrentDivisionNumber(),
                $endPoint
            ]);
        }

        return implode('/', [
            $this->getApiUrl(),
            $endPoint
        ]);
    }


    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }


    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @param mixed $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @param callable $callback
     */
    public function setTokenUpdateCallback($callback) {
        $this->tokenUpdateCallback = $callback;
    }


    /**
     * Parse the reponse in the Exception to return the Exact error messages
     * @param Exception $e
     * @throws ApiException
     */
    private function parseExceptionForErrorMessages(Exception $e)
    {
        if (! $e instanceof BadResponseException) {
            throw new ApiException($e->getMessage());
        }

        $response = $e->getResponse();
        Psr7\rewind_body($response);
        $responseBody = $response->getBody()->getContents();
        $decodedResponseBody = json_decode($responseBody, true);

        if (! is_null($decodedResponseBody) && isset($decodedResponseBody['error']['message']['value'])) {
            $errorMessage = $decodedResponseBody['error']['message']['value'];
        } else {
            $errorMessage = $responseBody;
        }

        throw new ApiException('Error ' . $response->getStatusCode() .': ' . $errorMessage);
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    private function getApiUrl()
    {
        return $this->baseUrl . $this->apiUrl;
    }

    /**
     * @return string
     */
    private function getTokenUrl()
    {
        return $this->baseUrl . $this->tokenUrl;
    }

    /**
     * Set base URL for different countries according to
     * https://developers.exactonline.com/#Exact%20Online%20sites.html
     *
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $authUrl
     */
    public function setAuthUrl($authUrl)
    {
        $this->authUrl = $authUrl;
    }

    /**
     * @param string $tokenUrl
     */
    public function setTokenUrl($tokenUrl)
    {
        $this->tokenUrl = $tokenUrl;
    }
}