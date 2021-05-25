<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefModCV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'reprogramados';
        Schema::table($nTable, function (Blueprint $table) {
            $table->integer('orden_servicio_id')->unsigned()->nullable();
            $table->foreign('orden_servicio_id')->references('id')->on('orden_servicios');

        });

        $nTable = 'orden_servicios';
        Schema::table($nTable, function (Blueprint $table) {
            $table->integer('comercial_id')->unsigned()->nullable();
        });

        $nTable = 'solicitud_servicios';
        Schema::table($nTable, function (Blueprint $table) {
          $table->integer('usuarios_id_7')->unsigned()->nullable();
          $table->datetime('fecha_7')->nullable();
            $table->integer('comercial_id')->unsigned()->nullable();
            $table->char('verificado',1)->default(0);
            $table->char('realizado',1)->default(0);
        });

        $nTable = 'comercial';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->char('estado',1)->default(6);

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
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

        Schema::table('reprogramados', function (Blueprint $table) {
            $table->dropForeign(['orden_servicio_id']);
            $table->dropColumn('orden_servicio_id');
        });
        Schema::table('solicitud_servicios', function (Blueprint $table) {
          $table->dropColumn('comercial_id');
          $table->dropColumn('verificado');
          $table->dropColumn('realizado');
          $table->dropColumn('usuarios_id_7');
          $table->dropColumn('fecha_7');

      });

      Schema::table('orden_servicios', function (Blueprint $table) {
        $table->dropColumn('comercial_id');
    });

      Schema::dropIfExists('comercial');
        Schema::enableForeignKeyConstraints();
    }

}
