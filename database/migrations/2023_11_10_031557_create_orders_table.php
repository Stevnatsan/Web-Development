<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('status', ['OPEN','REJECTED', 'AWAITING PAYMENT', 'ON GOING','FINISHED']);
            $table->dateTime('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->double('membership_discount')->nullable();
            $table->double('total_price');
            $table->integer('total_quantity');
            $table->text('address');
            $table->dateTime('due_date');
            $table->foreignId('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onUpdate('cascade')->onDelete('cascade');
            $table->double('nego_price')->nullable();
            $table->enum('nego_status', ['REJECTED', 'ACCEPTED'])->nullable();
            $table->string('payment_proof')->nullable();
            $table->enum('payment_proof_status', ['REJECTED', 'APPROVED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
