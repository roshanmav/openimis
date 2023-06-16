<?php

namespace Insurance\Openimis\Http\Services;

use GuzzleHttp\Client;
use Insurance\Openimis\Traits\OpenImisLogs;
use GuzzleHttp\Exception\BadResponseException;

class OpenImisService
{
    // https://imis.hib.gov.np/api/api_fhir

    use OpenImisLogs;

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
            'Authorization' => ['Basic ' . $this->getAuthUser()],
            'Content-Type' => 'application/json',
            $this->header => $this->header_value,
        ];
    }

    public function getAuthUser()
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    public function httpRequest($q, $payloadName = [], $method = 'get', $message,$insurance , $eligibilityRequest = 'EligibilityRequest')
    {

        $URI = $this->endpoint . '/' . $q;
        $options['headers'] = $this->getHeader();
        if (count($payloadName) > 0) {
            $options['json'] = $payloadName;
        }

        $options['http_errors'] = true; // for get exception y api response
        $http = new Client([
            'defaults' => [
                'exceptions' => false,
            ],
            'connect_timeout' => false,
            'timeout' => 30.0, // set higher if timeout still happens
        ]);
        try {

            if ($method == 'POST' || $method == 'post') {
                $response = $http->post($URI, $options);
            } elseif ($method == 'GET' || $method == 'get') {
                $response = $http->get($URI, $options);
            }
            $code = $response->getStatusCode();
            $content = $response->getBody()->__toString();
            $r = json_decode($content, true);
            $this->storeLog(json_encode($payloadName), json_encode($r), $eligibilityRequest, $URI, 'Y',$message,$insurance);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // $exception = (string) $e->getResponse()->getBody();
            // $exception = json_decode($exception);
            // dd($e);
            // $code = $e->getResponse()->getStatusCode();
            // $r = [];
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $code = $e->getResponse()->getStatusCode();
                $r = json_decode((string) $response->getBody());
                $errorMessage = isset($r->issue[0]->details)?$r->issue[0]->details->text:$r->issue[0]->code;
         
                $this->storeLog(json_encode($payloadName),json_encode($r),$eligibilityRequest,$URI,'N',$errorMessage,$insurance);
            }

            // dd($e->getResponse()->getStatusCode());
            // 

        }



        // dd();
        // return $response;
        // 



        // 

        $data = [
            'code' => $code,
            'data' => $r,
        ];
        // dd($data);

        return $data;
    }
}
