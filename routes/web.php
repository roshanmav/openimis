<?php

use Illuminate\Support\Facades\Route;

Route::post('eligbility-request', 'OpeniMisController@eligibilityRequest');
Route::post('submit/claim', 'OpeniMisController@submitClaim');
Route::get('locations', 'OpeniMisController@getParLocations');
