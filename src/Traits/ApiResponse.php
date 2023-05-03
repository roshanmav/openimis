<?php

namespace Insurance\Openimis\Traits;

trait ApiResponse
{
    public function getResponseReturn($data, $hasRecord, $isError, $hasMsg)
    {
        if ($hasMsg) {
            return response()->json(['msg' => $data, 'has_record' => $hasRecord, 'is_error' => $isError]);
        }

        return response()->json(['data' => $data, 'has_record' => $hasRecord, 'is_error' => $isError]);
    }
}
