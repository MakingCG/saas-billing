<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanMeteredTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_metered_tiers', function (Blueprint $table) {
            $table->uuid('plan_metered_item_id')->index();
            $table->integer('first_unit')->default(1);
            $table->integer('last_unit')->nullable();
            $table->decimal('per_unit');
            $table->decimal('flat_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_metered_tiers');
    }
}
