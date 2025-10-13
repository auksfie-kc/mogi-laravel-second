<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up():void
    {
        Schema::create('request_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('rest1_start')->nullable();
            $table->time('rest1_end')->nullable();
            $table->time('rest2_start')->nullable();
            $table->time('rest2_end')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved'])->default('pending');  //初期値pending(未決定)とapproved(承認)の２つに限定
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
        Schema::dropIfExists('request_changes');
    }
}
