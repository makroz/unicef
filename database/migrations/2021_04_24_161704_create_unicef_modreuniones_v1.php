<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefModreunionesV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'lista_apoyos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);;
            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'sesion_familiares';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->date('fecha');
            $table->text('contenido');
            $table->text('hallazgos');
            $table->text('alertas');
            $table->text('acciones');
            $table->tinyInteger('nparticipantes')->default(0);;
            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->integer('beneficiario_id')->unsigned();
            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'sesion_grupales';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->date('fecha');
            $table->text('contenido');
            $table->text('hallazgos');
            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'asistentes_grupales';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('beneficiario_id')->unsigned();
            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');

            $table->integer('sesion_grupal_id')->unsigned();
            $table->foreign('sesion_grupal_id')->references('id')->on('sesion_grupales');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'requiere_apoyos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('beneficiario_id')->unsigned();
            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');

            $table->integer('lista_apoyo_id')->unsigned();
            $table->foreign('lista_apoyo_id')->references('id')->on('lista_apoyos');

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

        Schema::dropIfExists('requiere_apoyos');
        Schema::dropIfExists('asistentes_grupales');
        Schema::dropIfExists('sesion_grupales');
        Schema::dropIfExists('sesion_familiares');
        Schema::dropIfExists('lista_apoyos');

        Schema::enableForeignKeyConstraints();
    }
}
