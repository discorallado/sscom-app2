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
        Schema::create('manager_outdates', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',2);
            $table->string('tipo_doc');
            $table->text('file')->nullable();
            $table->text('num_doc')->nullable();
            $table->date('date');
            $table->integer('excento');
            $table->integer('neto');
            $table->text('observaciones')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('manager_customer_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('manager_customer_id')->references('id')->on('manager_customers')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manager_outdates');
    }
};
