<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedirectsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redirects', static function (Blueprint $table) {
            $table->bigIncrements('id');

            $connection = config('database.default');

            $collation = explode(
                '_',
                $table->collation ?: config("database.connections.$connection.collation", 'utf8mb4_unicode_ci'), 2
            )[0]
                ?: 'utf8mb4';

            $table->string('old_url')->collation($collation . '_bin')->unique();
            $table->string('new_url')->collation($collation . '_bin')->nullable();
            $table->smallInteger('status')->default(301)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
}
