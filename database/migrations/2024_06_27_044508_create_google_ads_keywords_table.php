<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('google_keyword', function (Blueprint $table) {
            $table->id();
            $table->date('dated');
            $table->unsignedBigInteger('campaign_id');
            $table->string('campaign_name');
            $table->string('campaign_status');
            $table->unsignedBigInteger('ad_group_id');
            $table->string('ad_group_name');
            $table->string('ad_group_status');
            $table->string('keyword_match_type');
            $table->string('keyword');
            $table->string('device');
            $table->unsignedBigInteger('impressions');
            $table->unsignedBigInteger('clicks');
            $table->float('cost', 2);
            $table->string('account_name');
            $table->timestamps();

            $table->foreign('account_name')->references('account_name')->on('google_ads_accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('google_keyword');
    }
};
