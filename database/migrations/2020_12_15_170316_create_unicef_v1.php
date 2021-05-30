<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable='distritos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='entidades';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='categ';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('obs', 250)->nullable();
            $table->char('cant', 1)->nullable();
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='beneficiarios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->integer('epsa')->unsigned();
            $table->char('autoriza', 1)->nullable();
            $table->char('protec', 1)->nullable();
            $table->string('dir', 250)->nullable();
            $table->point('coord')->nullable();
            $table->char('nivel', 1)->default('0');
            $table->char('status', 1)->default('1');

            $table->integer('distritos_id')->unsigned()->nullable();
            $table->foreign('distritos_id')->references('id')->on('distritos');
            $table->integer('entidades_id')->unsigned()->nullable();
            $table->foreign('entidades_id')->references('id')->on('entidades');
            $table->integer('rutas_id')->unsigned()->nullable();
            $table->foreign('rutas_id')->references('id')->on('rutas');
            $table->index('coord');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='rutas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('descrip', 250)->nullable();
            $table->char('status', 1)->default('1');

            $table->integer('usuarios_id')->unsigned();
            $table->foreign('usuarios_id')->references('id')->on('usuarios');

            $table->timestamps();
            $table->softDeletes();
        });

       
        $nTable='preguntas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('pregunta',250);
            $table->char('tipo',1)->default('1');
            $table->tinyInteger('orden')->default(0);
            $table->char('status', 1)->default('1');

            $table->integer('categ_id')->unsigned();
            $table->foreign('categ_id')->references('id')->on('categ');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='ruteos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->datetime('fec_cerrado')->nullable();
            $table->datetime('fec_sincro')->nullable();
            $table->datetime('fec_verif')->nullable();
            $table->integer('verif_id')->unsigned()->nullable();
            $table->tinyInteger('semana')->default(0);
            $table->text('obs')->nullable();
            $table->point('gps_open');
            $table->point('gps_close')->nullable();
            $table->integer('open_id')->unsigned();
            $table->integer('close_id')->unsigned()->nullable();
            $table->char('estado', 1)->default('0');
            $table->char('status', 1)->default('1');

            $table->integer('rutas_id')->unsigned();
            $table->foreign('rutas_id')->references('id')->on('rutas')->onDelete('cascade');
            $table->integer('usuarios_id')->unsigned();
            $table->foreign('usuarios_id')->references('id')->on('usuarios');

            $table->timestamps();
            $table->softDeletes();
            $table->index('gps_open');
        });

        $nTable='evaluaciones';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            //$table->datetime('fecha');
            $table->point('coord');
            $table->text('obs')->nullable();
            $table->datetime('fec_verif')->nullable();
            $table->integer('verif_id')->unsigned()->nullable();
            $table->char('estado', 1)->default('0'); 
            $table->char('status', 1)->default('1');

            $table->integer('ruteos_id')->unsigned();
            $table->foreign('ruteos_id')->references('id')->on('ruteos')->onDelete('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade');
            $table->integer('usuarios_id')->unsigned();
            $table->foreign('usuarios_id')->references('id')->on('usuarios');

            $table->unique(['ruteos_id','beneficiarios_id']);

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='solicitud_servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->integer('usuarios_id_1')->unsigned()->nullable();
            $table->datetime('fecha_1')->nullable();
            $table->integer('usuarios_id_2')->unsigned()->nullable();
            $table->datetime('fecha_2')->nullable();
            $table->integer('usuarios_id_3')->unsigned()->nullable();
            $table->datetime('fecha_3')->nullable();
            $table->integer('usuarios_id_4')->unsigned()->nullable();
            $table->datetime('fecha_4')->nullable();
            $table->integer('usuarios_id_5')->unsigned()->nullable();
            $table->datetime('fecha_5')->nullable();
            $table->integer('usuarios_id_6')->unsigned()->nullable();
            $table->datetime('fecha_6')->nullable();
            $table->tinyInteger('cant'); 
            $table->char('estado', 1)->default('0');
            $table->char('status', 1)->default('1');

            $table->integer('servicios_id')->unsigned();
            $table->foreign('servicios_id')->references('id')->on('servicios')->onDelete('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade');
            $table->integer('evaluaciones_id')->unsigned()->nullable();
            $table->foreign('evaluaciones_id')->references('id')->on('evaluaciones')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='respuestas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->integer('r_n')->nullable(); 
            $table->decimal('r_d',10,2)->nullable(); 
            $table->string('r_s',250)->nullable(); 
            $table->char('status', 1)->default('1');

            $table->integer('preguntas_id')->unsigned();
            $table->foreign('preguntas_id')->references('id')->on('preguntas')->onDelete('cascade');
            $table->integer('evaluaciones_id')->unsigned();
            $table->foreign('evaluaciones_id')->references('id')->on('evaluaciones')->onDelete('cascade');
//            $table->integer('beneficiarios_id')->unsigned();
//            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onUpdate('cascade');

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

        Schema::dropIfExists('distritos');
        Schema::dropIfExists('entidades');
        Schema::dropIfExists('categ');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('beneficiarios');
        Schema::dropIfExists('rutas');
        Schema::dropIfExists('ruta_beneficiario');
        Schema::dropIfExists('ruteos');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('solicitud_servicios');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('respuestas');
        Schema::enableForeignKeyConstraints();
    }
}
