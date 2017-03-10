<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{

    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('parent_id')->nullable();
            $table->string('key')->nullable();
            $table->string('path', 4080)->nullable();
            $table->string('ext')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('mime')->nullable();
            $table->bigInteger('width')->nullable();
            $table->bigInteger('height')->nullable();
            $table->text('settings')->nullable();
            $table->integer('site_id')->nullable();
            $table->timestamps();

            $table->index([ 'parent_id' ]);
        });
    }


    public function down()
    {
        Schema::drop('files');
    }
}
