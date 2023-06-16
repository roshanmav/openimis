<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OPENIMIS USERNAME OF HOSPITAL
    |--------------------------------------------------------------------------
    */
    'ins_username' => env('INS_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | OPENIMIS PASSWORD OF HOSPITAL
    |--------------------------------------------------------------------------
    */

    'ins_password' => env('INS_PASSWORD'),


    /*
    |--------------------------------------------------------------------------
    | OPENIMIS ENDPOINT OF IMIS API IN DEFAULT 'https://imis.hib.gov.np/api/api_fhir'
    |--------------------------------------------------------------------------
    */

    // 'ins_endpoint' => 'https://imis.hib.gov.np/api/api_fhir',


    /*
    |--------------------------------------------------------------------------
    | OPENIMIS HEADER FOR REMOTE-USER OF HOSPITAL
    |--------------------------------------------------------------------------
    */

    // 'ins_header' => 'remote-user',
    'ins_header_value' => env('INS_HEADER_VALUE'),



    // location id 
     // use https://imis.hib.gov.np/api/api_fhir/Location find your hospital name  and use ID  ( "id": <uuid>) 

     'ins_location_id' => env('INS_LOCATION_ID'),

    // Practitioner
    // use https://imis.hib.gov.np/api/api_fhir/PractitionerRole and find location id inside location : [{reference:"location/<uuid>"}]
    'ins_parctitiner_id' => env('INS_PARCTITINER_ID')


]

    ?>