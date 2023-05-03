<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenimisClaimLogsTable extends Migration
{
    public function up()
    {
        Schema::create('openimis_claim_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('insurance_id');
            $table->string('claim_id');
            $table->string('claim_uuid');
            $table->longText('request');
            $table->longText('response');
            $table->enum('success_status', ['Y', 'N'])->default('N');
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
        Schema::dropIfExists('openimis_claim_logs');
    }
}
