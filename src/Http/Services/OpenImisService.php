<?php

namespace Insurance\Openimis\Http\Services;

use GuzzleHttp\Client;

class OpenImisService
{
    // https://imis.hib.gov.np/api/api_fhir

    protected $endpoint = 'https://imis.hib.gov.np/api/api_fhir';
    protected $username = '';
    protected $password = '';
    protected $header = 'remote-user';
    protected $header_value = '';

    protected $response;

    public function __construct()
    {
        $this->username = config('openimis.ins_username');
        $this->password = config('openimis.ins_password');
        $this->header_value = config('openimis.ins_header_value');
    }

    public function getHeader()
    {
        // return $this->getAuthUser();
        return [
            'Authorization' => ['Basic '.$this->getAuthUser()],
            'Content-Type' => 'application/json',
            $this->header => $this->header_value,
        ];
    }

    public function getAuthUser()
    {
        return base64_encode($this->username.':'.$this->password);
    }

    public function httpRequest($q, $payloadName = [], $method = 'get')
    {
        $URI = $this->endpoint.'/'.$q;
        $options['headers'] = $this->getHeader();
        if (count($payloadName) > 0) {
            $options['json'] = $payloadName;
        }
        $options['http_errors'] = false; // for get exception y api response
        $http = new Client([
            'defaults' => [
                'exceptions' => false,
            ],
            'connect_timeout' => false,
            'timeout' => 30.0, // set higher if timeout still happens
        ]);
        // dd($method,$URI,$options);
        $response = $http->request($method, $URI, $options);
        // return $response;
        $content = $response->getBody()->__toString();
        $r = json_decode($content, true);

        $data = [
            'code' => $response->getStatusCode(),
            'data' => $r,
        ];

        return $data;
    }
}
