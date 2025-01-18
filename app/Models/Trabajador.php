<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    use HasFactory;
    protected $table = 'trabajadores';

    protected $fillable = ['nombre', 'apellidos', 'dni', 'fecha_nacimiento', 'foto', 'usuario_id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function puestosLaborales()
    {
        return $this->belongsToMany(PuestoLaboral::class, 'puesto_trabajador', 'trabajador_id', 'puesto_id');
    }

    
}
