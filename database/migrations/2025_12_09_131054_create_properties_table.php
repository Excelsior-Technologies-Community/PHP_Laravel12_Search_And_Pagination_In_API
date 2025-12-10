<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); // auto-increment ID
            $table->string('title'); // property title
            $table->text('description')->nullable(); // description can be null
            $table->decimal('price', 12, 2)->default(0); // price with two decimals
            $table->string('location')->nullable(); // location string
            $table->unsignedBigInteger('created_by')->nullable(); // created by user id (optional)
            $table->unsignedBigInteger('updated_by')->nullable(); // updated by user id (optional)
            $table->enum('status',['active','inactive','deleted'])->default('active'); // active/inactive
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
