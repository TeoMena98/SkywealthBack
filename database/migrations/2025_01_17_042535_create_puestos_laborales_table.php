<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        DB::table('puestos_laborales')->insert([
            [
                'nombre' => 'Gerente',
                'codigo' => 'G1',
                'importancia' => 1,
                'es_jefe' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Administrador',
                'codigo' => 'Admin1',
                'importancia' => 2,
                'es_jefe' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Tecnico',
                'codigo' => 'T1',
                'importancia' => 3,
                'es_jefe' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Auxiliar',
                'codigo' => 'Au1',
                'importancia' => 4,
                'es_jefe' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('puesto_trabajador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->foreignId('puesto_id')->constrained('puestos_laborales')->onDelete('cascade');
            $table->timestamps();
        });

        DB::table('puesto_trabajador')->insert([
            'trabajador_id' => 1, 
            'puesto_id' => 1,  
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
