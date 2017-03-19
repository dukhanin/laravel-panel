<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SampleProducts extends Migration
{

    public function up()
    {
        Schema::create('sample_products', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivired')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('images')->nullable();
            $table->text('settings')->nullable();
            $table->tinyInteger('enabled')->nullable();
            $table->integer('index')->nullable();
            $table->string('section_id')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::drop('sample_products');
    }
}
