<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        DB::table('usuarios')->insert([
            'nombre' => 'Admin',
            'apellidos' => 'Sistema',
            'email' => 'admins@test.com',
            'password' => '$2a$12$TlDsd7s0KR50Qkzi.TnIj.vf9YzauWpdNysbyuuzLazzF2oBZH5pK', // Contraseña encriptada - 123
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
        Schema::dropIfExists('usuarios');
    }
}
