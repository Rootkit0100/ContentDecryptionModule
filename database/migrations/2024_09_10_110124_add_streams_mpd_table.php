<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStreamsMpdTable extends Migration
{

    public function up()
    {
        Schema::create('streams_mpd', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('server_id')->index();
            $table->integer('rtmp_server_id')->index();
            $table->string('name')->nullable();
            $table->string('url', 3000);
            $table->string('decryption_key', 255);
            $table->string('rtmp_url', 1000);
            $table->string('status', 50)->nullable();
            $table->integer('pid')->nullable();
            $table->integer('ffmpeg_pid')->nullable();
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
        Schema::dropIfExists('streams_mpd');
    }
}
