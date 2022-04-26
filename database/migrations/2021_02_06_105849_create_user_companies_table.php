<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_companies', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('type');
            $table->string('name');
            $table->text('address');
            $table->integer('province');
            $table->integer('city');
            $table->integer('kecamatan');
            $table->text('contact');
            $table->string('owner');
            $table->text('owner_contact');
            $table->timestamps();
            $table->integer('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_companies');
    }
}
