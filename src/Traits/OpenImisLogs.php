<?php

namespace Insurance\Openimis\Traits;

use DB;
use Carbon\Carbon;
trait OpenImisLogs
{

    public function storeLog($req,$res,$responseType,$url,$successStatus,$message,$insurance){
        $data = [
            "request"=>$req,
            "response"=>$res,
            "response_type"=>$responseType,
            "url"=>$url,
            "audit_id"=>0,
            "success_status"=>$successStatus,
            "created_at"=>Carbon::now(),
            'message'=>$message,
            'insurance_id'=>$insurance['insurance_id'],
            'claim_id'=>$insurance['claim_id']
        ];
        // dd($data);
        $store = DB::table('openimis_logs')->insert($data);
        if ($store) {
            return true;
        }
        return false;

        // $table->string('insurance_id');
        // $table->string('claim_id');
    }
}
