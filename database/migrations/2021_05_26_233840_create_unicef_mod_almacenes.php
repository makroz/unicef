<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnicefModAlmacenes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $nTable = 'mat_categ';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('descrip', 250)->nullable();

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'ubicaciones';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->string('descrip', 250)->nullable();

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'subtipos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name', 250);
            $table->tinyInteger('tipo')->unsigned();

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'materiales';
        Schema::table($nTable, function (Blueprint $table) {
            $table->integer('stock')->default('0');
            $table->integer('min_stock')->default('0');
            $table->decimal('costo', 10, 2)->default('0');
            $table->decimal('precio', 10, 2)->default('0');
            $table->char('lnota', 1)->default('1');

            $table->integer('mat_categ_id')->unsigned()->nullable();
            $table->foreign('mat_categ_id')->references('id')->on('mat_categ');
            $table->integer('ubicacion_id')->unsigned()->nullable();
            $table->foreign('ubicacion_id')->references('id')->on('ubicaciones');

        });

        $nTable = 'movimientos';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->tinyInteger('tipo')->unsigned();
            $table->string('ref', 25)->nullable();
            $table->string('obs', 250)->nullable();

            $table->integer('subtipo_id')->unsigned()->nullable();
            $table->foreign('subtipo_id')->references('id')->on('subtipos');

            $table->char('status', 1)->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $nTable = 'mov_det';
        Schema::dropIfExists($nTable);
        Schema::create($nTable, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->tinyInteger('tipo')->unsigned();
            $table->integer('cant');
            $table->string('obs', 250)->nullable();

            $table->integer('movimiento_id')->unsigned()->nullable();
            $table->foreign('movimiento_id')->references('id')->on('movimientos');

            $table->integer('material_id')->unsigned()->nullable();
            $table->foreign('material_id')->references('id')->on('materiales');

            $table->char('status', 1)->default('1');
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
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropForeign(['mat_categ_id', 'ubicacion_id']);
            $table->dropColumn(['stock', 'min_stock', 'costo', 'precio', 'lnota', 'mat_categ_id', 'ubicacion_id']);
        });
        Schema::dropIfExists('mat_categ');
        Schema::dropIfExists('ubicaciones');
        Schema::dropIfExists('subtipos');
        Schema::dropIfExists('movimientos');
        Schema::dropIfExists('mov_det');
        Schema::enableForeignKeyConstraints();
    }
}
