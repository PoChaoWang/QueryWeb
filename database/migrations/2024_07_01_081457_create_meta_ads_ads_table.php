<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meta_ads_ads', function (Blueprint $table) {
            $table->id();
            $table->date('dated');
            $table->unsignedBigInteger('campaign_id');
            $table->string('campaign_name');
            $table->string('adset_id');
            $table->string('adset_name');
            $table->unsignedBigInteger('ad_id');
            $table->string('name');
            $table->string('ad_status');
            $table->string('device');
            $table->unsignedBigInteger('impressions');
            $table->unsignedBigInteger('link_clicks');
            $table->decimal('spend', 10, 2);
            $table->string('account_name');
            $table->timestamps();

            $table->foreign('account_name')->references('account_name')->on('meta_ads_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_ads_ads');
    }
};
