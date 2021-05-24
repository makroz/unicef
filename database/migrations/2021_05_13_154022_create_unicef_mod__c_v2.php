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

        Schema::enableForeignKeyConstraints();
    }

}
