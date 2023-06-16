<?php

namespace Insurance\Openimis\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Insurance\Openimis\Http\Services\OpenImisService;
use Insurance\Openimis\Traits\ApiResponse;
use PSpell\Config;

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

    public function getPatientUUID(Request $request,$insurance)
    {
        $httpRequestResponse = $this->openimisService->httpRequest('Patient?identifier=' . $request->insurance_no, [], 'GET','identify patient done',$insurance);
        if ($httpRequestResponse['code'] == 200) {
            if ($httpRequestResponse['data']['total'] > 0) {
                $response = $httpRequestResponse['data']['entry'][0];
                $uuid = $httpRequestResponse['data']['entry'][0]['resource']['id'];

                DB::table($this->patientTable)->insert(
                    [
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
                'reference' => 'Patient/' . $request->insurance_no,
            ],
        ];
        $insurance = [
            "insurance_id"=>$request->insurance_no,
            "claim_id"=>""
        ];
        $httpRequestResponse = $this->openimisService->httpRequest('EligibilityRequest/', $payloadName, 'POST','Successfully checked the patient eligiblity',$insurance);


        if ($httpRequestResponse['data'] != null) {
            if ($httpRequestResponse['data']['resourceType'] == 'EligibilityResponse' && $httpRequestResponse['code'] == 201) {
                if (isset($httpRequestResponse['data']['insurance'])) {
                    $searchPatient = DB::table($this->patientTable)->where('insurance_id', $request->insurance_no)->first();

                    if ($searchPatient) {
                        DB::table($this->patientTable)->where('insurance_id', $request->insurance_no)->update(['updated_at' => Carbon::now(), 'call_count' => $searchPatient->call_count + 1]);
                        $uuid = $searchPatient->insurance_uuid;
                    } else {
                        $patient = $this->getPatientUUID($request,$insurance);
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
            $request['practitioner'] = config('openimis.ins_parctitiner_id');
            $request['location_uuid'] = config('openimis.ins_location_id');
            $getClaimRequest = $this->getClaimRequest($request);

            $insurance = [
                "insurance_id"=>$request['insurance_no'],
                "claim_id"=>$request['claim_no']
            ];
            // dd($insurance);
            $httpRequestResponse = $this->openimisService->httpRequest('Claim/', $getClaimRequest, 'POST','Claim has been made with ' . $request['claim_no'] . ' claim number.',$insurance,'Claim');
             
            // dd($httpRequestResponse);
            if ($httpRequestResponse['code'] == 200 || $httpRequestResponse['code'] == 201) {
                return $this->getResponseReturn($httpRequestResponse['data'] ,false, true, false,$httpRequestResponse['code']);
            }else{
                
               if(isset($httpRequestResponse['data']->issue[0]->details)){
                return $this->getResponseReturn($httpRequestResponse['data']->issue[0]->details->text, true, true, true,$httpRequestResponse['code']);
               }else{
                return $this->getResponseReturn($httpRequestResponse['data']->issue[0]->code, true, true, true,$httpRequestResponse['code']);
               }
            }
        } catch (\Throwable $th) {
            return $this->getResponseReturn('Please try agian!', true, true, true,500);
        }
    }


    protected function getClaimRequest($data)
    {
        $currentdate = Carbon::now()->format('Y-m-d');
        $identifer =  [
            [
                "type" => [
                    "coding" => [
                        [
                            "code" => "ACSN",
                            "system" => "https://hl7.org/fhir/valueset-identifier-type.html"
                        ]
                    ]
                ],
                "use" => "usual",
                "value" => "78F8F799-4534-48AC-9C3F-A7BA57BCFE3A"

            ],
            [
                "type" => [
                    "coding" => [
                        [
                            "code" => "MR",
                            "system" => "https://hl7.org/fhir/valueset-identifier-type.html"
                        ]
                    ]
                ],
                "use" => "usual",
                "value" => $data['claim_no']

            ]
        ];

        return [
            'resourceType' => 'Claim',
            'billablePeriod' => [
                'end' => $currentdate,
                'start' => $currentdate,
            ],
            'created' => $currentdate,
            'enterer' => [
                'reference' => "Practitioner/" . $data['practitioner'],
            ],
            'facility' => [
                'reference' => 'Location/' . $data['location_uuid'], // location uuid
            ],
            'id' => $data['location_uuid'], // claim uuid here
            'diagnosis' => $data['diagnosis'],
            'identifier' => $identifer,
            'item' => $data['item'],
            'total' => [
                'value' => $data['total_amount'], // totalamount to claimed
            ],
            'patient' => [
                'reference' => 'Patient/' . $data['insurance_uuid'], // insurance uuid
            ],
            'type' => [
                'text' => $data['type'],
            ],
        ];
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
