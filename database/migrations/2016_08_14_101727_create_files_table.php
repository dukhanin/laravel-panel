<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('key')->nullable();
            $table->string('path', 4080)->nullable();
            $table->string('ext')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('width')->nullable();
            $table->unsignedBigInteger('height')->nullable();
            $table->text('settings')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->timestamps();

            $table->index(['parent_id']);
        });
    }

    public function down()
    {
        Schema::drop('files');
    }
}
