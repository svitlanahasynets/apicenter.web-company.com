<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * Mews
 *
 * Mews is a class to query Moodle REST webservices
 */
class Mews
{
    private $platform_address;

    private $access_token;

    private $client_token;

    /**
     * Constructor
     *
     * @param string $platform_address Base address of the MEWS platform
     * @param string $client_token Token identifying the client application
     * @param string $access_token Access token of the client application
     */
    public function __construct($platform_address = null, $client_token = null, $access_token = null)
    {
        $this->platform_address = $platform_address;
        $this->client_token     = $client_token;
        $this->access_token     = $access_token;
    }


    /**
     * getCustomers get created customers in time lapse
     *
     * @param string $time_filter Time filter of the interval
     * @param string $start_utc Start of the interval in UTC timezone in ISO 8601 format
     * @param string $end_utc End of the interval in UTC timezone in ISO 8601 format
     */
    public function getCustomers($time_filter, $start_utc, $end_utc)
    {
        $ch = curl_init($this->platform_address."/api/connector/v1/customers/getAll");
        // echo $start_utc . '<br>' . $end_utc . '<br>';
        $saveData = [
            "ClientToken" => $this->client_token,
            "AccessToken" => $this->access_token,
            "TimeFilter"  => $time_filter,
            "StartUtc"    => $start_utc,
            "EndUtc"      => $end_utc
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));

        $result = curl_exec($ch);
        $result = json_decode($result, true);

        if (isset($result['Message'])) {
            return ['status' => false, 'message' => $result['Message']];
        }

        return ['status' => true, 'data' => $result['Customers']];
    }

    private function dd($data)
    {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }
}