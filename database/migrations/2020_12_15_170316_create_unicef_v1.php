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
            $table->string('nom', 60);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='entidades';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('nom', 60);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='categ';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('nom', 60);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('nom', 60);
            $table->string('obs', 250)->nullable();;
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='beneficiarios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('nom', 60);
            $table->integer('epsa')->unsigned();
            $table->char('autoriza', 1)->default('1');
            $table->char('protec', 1)->default('1');
            $table->string('dir', 250)->nullable();
            $table->string('lat', 32)->nullable();
            $table->string('long', 32)->nullable();
            $table->char('nivel', 1)->default('1');
            $table->char('status', 1)->default('1');

            $table->integer('distritos_id')->unsigned();
            $table->foreign('distritos_id')->references('id')->on('distritos')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('entidades_id')->unsigned();
            $table->foreign('entidades_id')->references('id')->on('entidades')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='rutas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->string('nom', 60);
            $table->string('desc', 250)->nullable();
            $table->char('status', 1)->default('1');

            $table->bigInteger('users_id')->unsigned();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='ruta_beneficiario';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->tinyInteger('orden');
            $table->char('status', 1)->default('1');

            $table->integer('rutas_id')->unsigned();
            $table->foreign('rutas_id')->references('id')->on('rutas')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade')->onUpdate('cascade');

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
            $table->foreign('categ_id')->references('id')->on('categ')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='evaluaciones';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->timestamp('fec_abierto');
            $table->timestamp('fec_cerrado')->nullable();
            $table->timestamp('fec_sincro')->nullable();
            $table->timestamp('fec_verif')->nullable();
            $table->tinyInteger('semana');
            $table->text('obs')->nullable();
            $table->point('cabierto');
            $table->point('ccerrado')->nullable();
            $table->char('estado', 1)->default('1');
            $table->char('status', 1)->default('1');

            $table->integer('rutas_id')->unsigned();
            $table->foreign('rutas_id')->references('id')->on('rutas')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('users_id')->unsigned();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('verif_id')->unsigned();
            $table->foreign('verif_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='solicitud_servicios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->timestamp('fecha');
            $table->tinyInteger('cant'); 
            $table->char('estado', 1)->default('1');
            $table->char('status', 1)->default('1');

            $table->integer('servicios_id')->unsigned();
            $table->foreign('servicios_id')->references('id')->on('servicios')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('users_id')->unsigned();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('evaluaciones_id')->unsigned();
            $table->foreign('evaluaciones_id')->references('id')->on('evaluaciones')->onDelete('cascade')->onUpdate('cascade');

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
            $table->foreign('preguntas_id')->references('id')->on('preguntas')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('evaluaciones_id')->unsigned();
            $table->foreign('evaluaciones_id')->references('id')->on('evaluaciones')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable='evaluaciones_beneficiarios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine ='InnoDB';

            $table->increments('id');
            $table->timestamp('fecha');
            $table->point('ubic');
            $table->text('obs')->nullable();
            $table->timestamp('fec_verif')->nullable();
            $table->char('estado', 1)->default('1'); 
            $table->char('status', 1)->default('1');

            $table->integer('evaluaciones_id')->unsigned();
            $table->foreign('evaluaciones_id')->references('id')->on('evaluaciones')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('beneficiarios_id')->unsigned();
            $table->foreign('beneficiarios_id')->references('id')->on('beneficiarios')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('verif_id')->unsigned();
            $table->foreign('verif_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('solicitud_servicios');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('evaluaciones_beneficiarios');

        Schema::enableForeignKeyConstraints();
    }
}
