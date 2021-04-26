<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefMod2V1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'dptos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'municipios';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->integer('dpto_id')->unsigned();
            $table->foreign('dpto_id')->references('id')->on('dptos')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'distritos';
        //Schema::dropIfExists($nTable);
        Schema::table($nTable, function (Blueprint $table) {
            $table->integer('municipio_id')->unsigned();
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');
        });

        $nTable = 'zonas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->integer('distrito_id')->unsigned();
            $table->foreign('distrito_id')->references('id')->on('distritos')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'epsas';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'descoms';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'parentescos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'est_civiles';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'niv_educativos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'ocupaciones';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'tipo_banos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'doc_firmados';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);;
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'info_metodos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);;
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'prob_sol_existentes';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('orden')->default(0);;
            $table->char('status', 1)->default('1');

            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'beneficiarios';
        //Schema::dropIfExists($nTable);
        Schema::table($nTable, function (Blueprint $table) {
            $table->string('manzano', 10)->nullable();
            $table->string('lote', 10)->nullable();
            $table->string('safsi', 10)->nullable();
            $table->tinyInteger('nfamilias')->default(1);
            $table->tinyInteger('npersonas')->default(1);
            $table->decimal('c_gob_municipal', 8, 2)->nullable();
            $table->decimal('c_gob_municipal_p', 5, 2)->nullable();
            $table->decimal('c_ong', 8, 2)->nullable();
            $table->decimal('c_ong_p', 5, 2)->nullable();
            $table->decimal('c_familias', 8, 2)->nullable();
            $table->decimal('c_familias_p', 5, 2)->nullable();
            $table->decimal('c_otra', 8, 2)->nullable();
            $table->decimal('c_otra_p', 5, 2)->nullable();

            $table->integer('dpto_id')->unsigned();
            $table->foreign('dpto_id')->references('id')->on('dptos');
            $table->integer('municipio_id')->unsigned();
            $table->foreign('municipio_id')->references('id')->on('municipios');
            $table->integer('zona_id')->unsigned()->nullable();
            $table->foreign('zona_id')->references('id')->on('zonas');
            $table->integer('descom_id')->unsigned()->nullable();
            $table->foreign('descom_id')->references('id')->on('descoms');
            $table->integer('epsa_id')->unsigned()->nullable();
            $table->foreign('epsa_id')->references('id')->on('epsas');
            $table->integer('tipo_bano_id')->unsigned()->nullable();
            $table->foreign('tipo_bano_id')->references('id')->on('tipo_banos');
            $table->integer('doc_firmado_id')->unsigned()->nullable();
            $table->foreign('doc_firmado_id')->references('id')->on('doc_firmados');
            $table->integer('info_metodo_id')->unsigned()->nullable();
            $table->foreign('info_metodo_id')->references('id')->on('info_metodos');
            $table->integer('prob_sol_existente_id')->unsigned()->nullable();
            $table->foreign('prob_sol_existente_id')->references('id')->on('prob_sol_existentes');
        });

        $nTable = 'familiares';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('edad')->nullable();
            $table->char('genero', 1)->nullable();
            $table->char('status', 1)->default('1');

            $table->integer('beneficiario_id')->unsigned();
            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');
            $table->integer('parentesco_id')->unsigned();
            $table->foreign('parentesco_id')->references('id')->on('parentescos');
            $table->integer('est_civil_id')->unsigned();
            $table->foreign('est_civil_id')->references('id')->on('est_civiles');
            $table->integer('niv_estudio_id')->unsigned();
            $table->foreign('niv_estudio_id')->references('id')->on('niv_estudios');
            $table->integer('ocupacion_id')->unsigned();
            $table->foreign('ocupacion_id')->references('id')->on('ocupaciones');

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

        Schema::dropIfExists('familiares');

        Schema::table('beneficiarios', function (Blueprint $table) {
            $table->dropColumn('manzano');
            $table->dropColumn('lote');
            $table->dropColumn('safsi');
            $table->dropColumn('nfamilias');
            $table->dropColumn('npersonas');
            $table->dropColumn('c_gob_municipal');
            $table->dropColumn('c_gob_municipal_p');
            $table->dropColumn('c_ong');
            $table->dropColumn('c_ong_p');
            $table->dropColumn('c_familias');
            $table->dropColumn('c_familias_p');
            $table->dropColumn('c_otra');
            $table->dropColumn('c_otra_p');

            $table->dropColumn('dpto_id');
            $table->dropForeign(['dpto_id']);
            $table->dropColumn('municipio_id');
            $table->dropForeign(['municipio_id']);
            $table->dropColumn('zona_id');
            $table->dropForeign(['zona_id']);
            $table->dropColumn('descom_id');
            $table->dropForeign(['descom_id']);
            $table->dropColumn('epsa_id');
            $table->dropForeign(['epsa_id']);
            $table->dropColumn('tipo_bano_id');
            $table->dropForeign(['tipo_bano_id']);
            $table->dropColumn('doc_firmado_id');
            $table->dropForeign(['doc_firmado_id']);
            $table->dropColumn('info_metodo_id');
            $table->dropForeign(['info_metodo_id']);
            $table->dropColumn('prob_sol_existente_id');
            $table->dropForeign(['prob_sol_existente_id']);
        });

        Schema::dropIfExists('prob_sol_existentes');
        Schema::dropIfExists('info_metodos');
        Schema::dropIfExists('doc_firmados');
        Schema::dropIfExists('tipo_banos');
        Schema::dropIfExists('ocupaciones');
        Schema::dropIfExists('niv_educativos');
        Schema::dropIfExists('est_civiles');
        Schema::dropIfExists('parentescos');
        Schema::dropIfExists('descoms');
        Schema::dropIfExists('epsas');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('municipios');
        Schema::dropIfExists('dptos');

        Schema::table('distritos', function (Blueprint $table) {
            $table->dropColumn('municipio_id');
            $table->dropForeign(['municipio_id']);
        });

        Schema::enableForeignKeyConstraints();
    }
}
