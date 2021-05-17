<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefModC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'medidas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('simbolo', 5);

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'forma_pagos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'control_calidades';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('descrip', 250)->nullable();
            $table->tinyInteger('orden')->default(0);;

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'materiales';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');
            $table->integer('medida_id')->unsigned();
            $table->foreign('medida_id')->references('id')->on('medidas');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'meteriales_servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            $table->integer('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materiales')->onDelete('cascade');

            $table->integer('servicio_id')->unsigned();
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'meteriales_usados';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->decimal('cant', 6, 2);

            $table->integer('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materiales');

            $table->integer('solicitud_servicio_id')->unsigned();
            $table->foreign('solicitud_servicio_id')->references('id')->on('solicitud_servicios');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'orden_servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('ref', 100);
            $table->char('foto', 1)->default(0);
            $table->string('obs', 250)->nullable();
            $table->char('estado', 1)->default(0);
            $table->char('status', 1)->default(1);

            $table->integer('recolector_id')->unsigned();
            $table->foreign('recolector_id')->references('id')->on('usuarios');
            $table->integer('forma_pago_id')->unsigned();
            $table->foreign('forma_pago_id')->references('id')->on('forma_pagos');
            $table->integer('beneficiario_id')->unsigned();
            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'control_solicitudes';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->tinyInteger('puntos')->default(0);
            $table->string('obs', 250)->nullable();
            $table->char('status', 1)->default(1);

            $table->integer('solicitud_servicio_id')->unsigned();
            $table->foreign('solicitud_servicio_id')->references('id')->on('solicitud_servicios');
            $table->integer('control_calidad_id')->unsigned();
            $table->foreign('control_calidad_id')->references('id')->on('control_calidades');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'reprogramados';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('obs', 250);
            $table->char('status', 1)->default(1);

            $table->integer('solicitud_servicio_id')->unsigned();
            $table->foreign('solicitud_servicio_id')->references('id')->on('solicitud_servicios');
            $table->integer('recolector_id')->unsigned();
            $table->foreign('recolector_id')->references('id')->on('usuarios');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'solicitud_servicios';
        Schema::table($nTable, function (Blueprint $table) {
            $table->string('obs', 250)->nullable();
            $table->integer('orden_servicios_id')->unsigned()->nullable();
            $table->foreign('orden_servicios_id')->references('id')->on('orden_servicios');

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

        Schema::table('solicitud_servicios', function (Blueprint $table) {
            $table->dropColumn('obs');
            $table->dropForeign(['orden_servicios_id']);
            $table->dropColumn('orden_servicios_id');

        });
        Schema::dropIfExists('reprogramados');
        Schema::dropIfExists('control_solicitudes');
        Schema::dropIfExists('orden_servicios');
        Schema::dropIfExists('meteriales_usados');
        Schema::dropIfExists('meteriales_servicios');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('control_calidades');
        Schema::dropIfExists('forma_pagos');
        Schema::dropIfExists('medidas');

        Schema::enableForeignKeyConstraints();
    }

}
