<?php

namespace Insurance\Openimis\Traits;

trait ApiResponse
{
    public function getResponseReturn($data, $hasRecord, $isError, $hasMsg,$code=200)
    {
        if ($hasMsg) {
            return response()->json(['msg' => $data, 'has_record' => $hasRecord, 'is_error' => $isError],$code);
        }

        return response()->json(['data' => $data, 'has_record' => $hasRecord, 'is_error' => $isError],$code);
    }
}
