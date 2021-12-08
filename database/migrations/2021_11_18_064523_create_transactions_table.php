<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->index();
            $table->uuid('user_id')->index();
            $table->enum('type', ['charge', 'credit', 'withdrawal']);
            $table->enum('status', ['completed', 'error', 'cancelled']);
            $table->string('plan_name');
            $table->string('driver');
            $table->string('reference')->nullable();
            $table->decimal('amount');
            $table->text('currency');
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
        Schema::dropIfExists('transactions');
    }
}
