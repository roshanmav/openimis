<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpenimisLogsTable extends Migration
{
    public function up()
    {
        Schema::create('openimis_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('request');
            $table->longText('response');
            $table->string('response_type');
            $table->string('message')->nullable();
            $table->string('url');
            $table->bigInteger('audit_id');
            $table->enum('success_status', ['Y', 'N'])->default('N');
            $table->string('insurance_id');
            $table->string('claim_id');
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
        Schema::dropIfExists('openimis_logs');
    }
}
