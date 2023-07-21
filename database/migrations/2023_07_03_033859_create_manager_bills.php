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
        Schema::create('manager_bills', function (Blueprint $table) {
            $table->id();

            $table->date('fecha')->nullable();
            $table->string('doc')->nullable();
            $table->string('file')->nullable();
            $table->text('descripcion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('manager_customer_id')->nullable();
            $table->unsignedBigInteger('manager_cotization_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('manager_customer_id')->references('id')->on('manager_customers')->nullOnDelete();
            $table->foreign('manager_cotization_id')->references('id')->on('manager_cotization')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_bills');
    }
};
