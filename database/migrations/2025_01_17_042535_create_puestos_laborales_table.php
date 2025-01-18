<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuestosLaboralesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puestos_laborales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->integer('importancia');
            $table->boolean('es_jefe')->default(false);
            $table->timestamps();
        });

        Schema::create('puesto_trabajador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->foreignId('puesto_id')->constrained('puestos_laborales')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('puestos_laborales');
    }
}
