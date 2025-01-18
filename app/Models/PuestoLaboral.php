<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoLaboral extends Model
{
    use HasFactory;
    protected $table = 'puestos_laborales';

    protected $fillable = ['nombre', 'codigo', 'importancia', 'es_jefe'];

    public function trabajadores()
    {
        return $this->belongsToMany(Trabajador::class, 'puesto_trabajador', 'puesto_id', 'trabajador_id');
    }
}
