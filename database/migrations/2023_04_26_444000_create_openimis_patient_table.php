<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenimisPatientTable extends Migration
{
    public function up()
    {
        Schema::create('openimis_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('insurance_id');
            $table->string('insurance_uuid');
            $table->longText('response');
            $table->integer('call_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openimis_patients');
    }
}
