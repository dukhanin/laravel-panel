<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PanelSampleSections extends Migration
{

    public function up()
    {
        Schema::create('panel_sample_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('image')->nullable();
            $table->text('settings')->nullable();
            $table->tinyInteger('enabled')->nullable();
            $table->integer('index')->nullable();
            $table->string('parent_id')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::drop('panel_sample_sections');
    }
}
