<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UnicefMod56V1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'check_categ';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'eventos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'vehiculos_marcas';
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

        $nTable = 'checks';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);
            $table->char('tipo', 1)->default('c'); //c=check, n=numero, t=texto, s=si/no

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'choferes';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('ci', 50)->unique();
            $table->string('dir', 250)->nullable();
            $table->string('tel', 250)->nullable();
            $table->char('lic', 1)->default('P');
            $table->date('fec_nac')->nullable();
            $table->tinyInteger('orden')->default(0);
            $table->char('tipo', 1)->default('c'); //c=check, n=numero,d=decimal, t=texto, s=si/no

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'vehiculos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('placa', 10)->unique();
            $table->string('serie', 50)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('capacidad', 100)->nullable();
            $table->smallInteger('cilindradas')->nullable();
            $table->smallInteger('anio')->nullable();

            $table->integer('marca_id')->unsigned();
            $table->foreign('marca_id')->references('id')->on('vehiculos_marcas');

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'check_diarios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->date('fecha');
            $table->time('salida');
            $table->time('regreso')->nullable();
            $table->integer('km_salida')->unsigned()->nullable();
            $table->integer('km_refreso')->unsigned()->nullable();
            $table->text('obs')->nullable();

            $table->integer('recolector_id')->unsigned();
            $table->foreign('recolector_id')->references('id')->on('usuarios');
            $table->integer('vehiculo_id')->unsigned();
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos');
            $table->integer('chofer_id')->unsigned();
            $table->foreign('chofer_id')->references('id')->on('choferes');
            $table->integer('salida_id')->unsigned();
            $table->foreign('salida_id')->references('id')->on('usuarios');
            $table->integer('llegada_id')->unsigned();
            $table->foreign('llegada_id')->references('id')->on('usuarios');

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'check_materiales';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('cant', 10)->unsigned()->default(0);

            $table->integer('diario_id')->unsigned();
            $table->foreign('diario_id')->references('id')->on('check_diarios');
            $table->integer('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materiales');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'check_det';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('resp', 250);

            $table->integer('diario_id')->unsigned();
            $table->foreign('diario_id')->references('id')->on('check_diarios');
            $table->integer('check_id')->unsigned();
            $table->foreign('check_id')->references('id')->on('checks');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'check_eventos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->text('detalle');

            $table->integer('diario_id')->unsigned();
            $table->foreign('diario_id')->references('id')->on('check_diarios');
            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')->references('id')->on('eventos');

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
        Schema::dropIfExists('check_categ');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('vehiculos_marcas');
        Schema::dropIfExists('checks');
        Schema::dropIfExists('choferes');
        Schema::dropIfExists('vehiculos');
        Schema::dropIfExists('check_diarios');
        Schema::dropIfExists('check_materiales');
        Schema::dropIfExists('check_det');
        Schema::dropIfExists('check_eventos');
        Schema::enableForeignKeyConstraints();
    }
}
