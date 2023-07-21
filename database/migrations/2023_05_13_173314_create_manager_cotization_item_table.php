<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manager_cotization_items', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->integer('precio_anotado');

            $table->unsignedBigInteger('cotization_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            $table->foreign('cotization_id')->references('id')->on('cotization')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('product')->nullOnDelete();

            $table->foreign('cotization_id')->references('id')->on('cotization')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
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
        Schema::dropIfExists('manager_cotizations_items');
    }
};
