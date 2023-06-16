Laravel OPENIMIS INSURANCE
========

## Installation

Run `composer require insurance/openimis`.

In your `config/app.php` file, add the Laravel API Key service provider to the end of the `providers` array.

```php
Insurance\Openimis\OpenImisServiceProvider::class,
```

Publish the migration and config fiels

    $ php artisan openimis:install

Run the migrations

    $ php artisan migrate --path=database/migrations/openimis
    
Route List 

    $ {host}/openimis/eligbility-request  Method=> POST Request=> {"insurance_no":"1234123123"}
    $ {host}/openimis/submit/claim  Method=> POST Request=> {"diagnosis":[{"diagnosisCodeableConcept":       {"coding":[{"code":"FA01"}]},"sequence":1,"type":[{"text":"icd_0"}]}],"identifier":[{"type":{"coding":[{"code":"ACSN","system":"https:\/\/hl7.org\/fhir\/valueset-identifier-type.html"}]},"use":"usual","value":"9E8616DB-D9DA-458C-9A9E-6F9682241C10"},{"type":{"coding":[{"code":"MR","system":"https:\/\/hl7.org\/fhir\/valueset-identifier-type.html"}]},"use":"usual","value":"366844"}],"item":[{"category":{"text":"service"},"quantity":{"value":1},"sequence":1,"service":{"text":"OPD 2"},"unitPrice":{"value":200}}],"total_amount":2000,"insurance_uuid":"1273asdjnlkasjdlas","type":"0"}
    $ {host}/openimis/locations  Method=> GET

Developed by: Roshan Shrestha
Senior Team Lead (Software Enigineer)
