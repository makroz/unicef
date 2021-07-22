<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnicefModLogger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::disableForeignKeyConstraints();
      
      $nTable = 'logger';
      Schema::dropIfExists($nTable);
      Schema::create($nTable, function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->increments('id');
          $table->tinyInteger('type')->default(0);
          $table->text('message');
          $table->Integer('usuario_id');
          $table->string('ip', 32);
          $table->text('token')->nullable();
          $table->mediumText('attrib')->nullable();
          $table->char('action', 1)->default('0');
          $table->char('app_id', 1)->default('0');
          $table->char('deletable', 1)->default('1');
          $table->char('status', 1)->default(1);
          $table->softDeletes();
          $table->timestamps();
      });
      Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::disableForeignKeyConstraints();

      Schema::dropIfExists('logger');

      Schema::enableForeignKeyConstraints();
    }
}
