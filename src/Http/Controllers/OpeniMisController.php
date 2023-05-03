<?php

namespace Insurance\Openimis\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Insurance\Openimis\Http\Services\OpenImisService;
use Insurance\Openimis\Traits\ApiResponse;

class OpeniMisController extends Controller
{
    use ApiResponse;
    protected $openimisService;

    protected $patientTable = 'openimis_patients';

    public function __construct(OpenImisService $OMS)
    {
        // dd();
        $this->openimisService = new $OMS();
    }

    public function getPatientUUID(Request $request)
    {
        $httpRequestResponse = $this->openimisService->httpRequest('Patient?identifier='.$request->insurance_no, [], 'GET');
        if ($httpRequestResponse['code'] = 200) {
            if ($httpRequestResponse['data']['total'] > 0) {
                $response = $httpRequestResponse['data']['entry'][0];
                $uuid = $httpRequestResponse['data']['entry'][0]['resource']['id'];

                DB::table($this->patientTable)->insert([
                    'insurance_id' => $request->insurance_no,
                    'insurance_uuid' => $uuid,
                    'response' => json_encode($response),
                    'created_at' => Carbon::now(),
                ]
                );

                return $uuid;
            }
        }

        return 0;
    }

    public function eligibilityRequest(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'insurance_no' => 'required|numeric',
        ]);
        if ($validator->fails()) {
        }
        $payloadName = [
            'resourceType' => 'EligibilityRequest',
            'patient' => [
                'reference' => 'Patient/'.$request->insurance_no,
            ],
        ];
        $httpRequestResponse = $this->openimisService->httpRequest('EligibilityRequest/', $payloadName, 'POST');

        if ($httpRequestResponse['data'] != null) {
            if ($httpRequestResponse['data']['resourceType'] == 'EligibilityResponse' && $httpRequestResponse['code'] == 201) {
                if (isset($httpRequestResponse['data']['insurance'])) {
                    $searchPatient = DB::table($this->patientTable)->where('insurance_id', $request->insurance_no)->first();

                    if ($searchPatient) {
                        DB::table($this->patientTable)->where('insurance_id', $request->insurance_no)->update(['updated_at' => Carbon::now(), 'call_count' => $searchPatient->call_count + 1]);
                        $uuid = $searchPatient->insurance_uuid;
                    } else {
                        $patient = $this->getPatientUUID($request);
                        if ($patient != '') {
                            $uuid = $patient;
                        }
                    }

                    return $this->getResponseReturn([
                            'uuid' => $uuid,
                            'balance' => $httpRequestResponse['data']['insurance'][0]['benefitBalance'][0],
                    ], true, false, false);
                } else {
                    return $this->getResponseReturn('No record(s) found', false, false, true);
                }
            } elseif ($httpRequestResponse['data']['resourceType'] == 'OperationOutcome' && $httpRequestResponse['code'] > 400) {
                return $this->getResponseReturn($httpRequestResponse['data']['issue'][0]['details']['text'], false, true, true);
            }
        } else {
            return $this->getResponseReturn('Something went wrong!', false, true, true);
        }
    }

    public function submitClaim(Request $request)
    {
        try {
            $request = $request->all();
            $request['practitioner'] = $this->getPractitionerFromLocation($request['location_uuid']);

            $getClaimRequest = $this->getClaimRequest($request);
            $data = [
                'request' => json_encode($getClaimRequest),
                'response' => 'aa',
                'insurance_id' => 'insurance_id',
                'claim_uuid' => 'claim_uuid',
                'claim_id' => 'claim_id',
                'success_status' => 'Y',
                'created_at' => Carbon::now(),
            ];

            // dd($getClaimRequest, $data);
            // $storeClaimLogs = $this->storeClaimLogs($data);

            return response()->json($getClaimRequest);
        } catch (\Throwable $th) {
            return $this->getResponseReturn('Please try agian!', false, false, true);
        }
    }

    protected function getPractitionerFromLocation($locationUUID)
    {
        $httpRequestResponse = $this->openimisService->httpRequest('PractitionerRole/', [], 'GET');
        if ($httpRequestResponse['data'] != null) {
            $location = 'Location/'.$locationUUID;
            $data = (array) $httpRequestResponse['data']['entry'];
            $found = array_filter($data, function ($v, $k) use ($location) {
                return $v['resource']['location'][0]['reference'] == $location;
            }, ARRAY_FILTER_USE_BOTH);
            $keys = array_keys($found);

            return $found[$keys[0]]['resource']['practitioner']['reference'];
        }
    }

    protected function getClaimRequest($data)
    {
        $currentdate = Carbon::now()->format('Y-m-d');

        return [
            'resourceType' => 'Claim',
            'billablePeriod' => [
                'end' => $currentdate,
                'start' => $currentdate,
            ],
            'created' => $currentdate,
            'enterer' => [
                'reference' => $data['practitioner'],
            ],
            'facility' => [
                'reference' => 'Location/'.$data['location_uuid'], // location uuid
            ],
            'id' => '9E8616DB-D9DA-458C-9A9E-6F9682241C10', // claim uuid here
            'diagnosis' => $data['diagnosis'],
            'identifier' => $data['identifier'],
            'item' => $data['item'],
            'total' => [
                'value' => $data['total_amount'], // totalamount to claimed
            ],
            'patient' => [
                'reference' => 'Patient/'.$data['insurance_uuid'], // insurance uuid
            ],
            'type' => [
                'text' => $data['type'],
            ],
        ];
    }

    public function getParLocations()
    {
        $httpRequestResponse = $this->openimisService->httpRequest('Location/', [], 'GET');
        $data = [];
        if ($httpRequestResponse['data'] != null) {
            $de = $httpRequestResponse['data']['entry'];
            foreach ($de as $key => $d) {
                array_push($data, [
                 'uuid' => $d['resource']['id'],
                 'name' => $d['resource']['name'],
                ]);
            }
        }

        return $this->getResponseReturn([
            'data' => $data,
        ], false, false, true);
    }

    protected function storeClaimLogs($data)
    {
        try {
            if (!$this->checkDupClaimId($data->claim_id)) {
                $store = DB::table('openimis_claim_logs')->insert($data);
                if ($store) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    protected function checkDupClaimId($claimId)
    {
        $checker = DB::table('openimis_claim_logs')->where('claim_id', $claimId)->first();

        return $checker ? true : false;
    }
}
