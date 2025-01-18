<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * La tabla asociada al modelo.
     *
     * Si el nombre de la tabla es diferente al plural del nombre del modelo,
     * se puede especificar aquí. En este caso, la tabla es 'usuarios'.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * Se especifican los campos de la tabla 'usuarios' que pueden ser 
     * asignados masivamente. Esto ayuda a proteger los campos que no
     * deberían ser modificados por un usuario.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'apellidos', 'email', 'password', // Campos que existen en la tabla
    ];

    /**
     * Los atributos que deberían ser ocultados para los arrays.
     *
     * Estos atributos no serán incluidos cuando el modelo sea convertido a un
     * array o JSON. Por ejemplo, la contraseña y el token de recuerdo deben
     * ser ocultados para evitar que se expongan de manera insegura.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Métodos requeridos por la interfaz JWTSubject.
     *
     * Estas funciones son necesarias para que el modelo funcione con JWT.
     * Se utilizan para obtener el identificador del usuario y los "claims" 
     * personalizados para el token JWT.
     */

    /**
     * Obtiene el identificador único del usuario.
     *
     * Este método se usa para recuperar el identificador del usuario y debe 
     * retornar el valor de la clave primaria.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Retorna la clave primaria del usuario
    }

    /**
     * Obtiene los "claims" personalizados para el token JWT.
     *
     * Los "claims" son información adicional que puede ser agregada al token.
     * Este método puede ser modificado para devolver datos adicionales, si se requiere.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // Retorna un array vacío, pero puede ser extendido si es necesario
    }
}
