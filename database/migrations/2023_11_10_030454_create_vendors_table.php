<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->text('description');
            $table->integer('rating');
            $table->string('phone')->nullable();
            $table->string('image')->nullable();
            $table->json('vendor_membership')->nullable();
            $table->rememberToken()->NULL;
            $table->enum('status', ['ACTIVE' , 'INACTIVE']);
            $table->foreignId('status_updated_by')->nullable();
            $table->foreign('status_updated_by')->references('id')->on('admins')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}
