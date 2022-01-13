<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedirectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->bigIncrements('id');

            $connection = config('database.default');

            $collation = explode('_', $table->collation ?: config("database.connections.{$connection}.collation"), 2)[0];

            $table->string('old_url')->collation($collation.'_bin')->unique();
            $table->string('new_url')->collation($collation.'_bin')->nullable();
            $table->smallInteger('status')->default(301)->index();

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
        Schema::dropIfExists('redirects');
    }
}
