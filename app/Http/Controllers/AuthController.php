<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario.
     *
     * Valida los datos de entrada, crea un nuevo usuario en la base de datos
     * y devuelve una respuesta con el mensaje de éxito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validación de los datos de entrada
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
        ]);

        // Crear el nuevo usuario y almacenarlo en la base de datos
        Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Contraseña cifrada
        ]);

        // Respuesta de éxito
        return response()->json(['message' => 'Usuario registrado correctamente'], 201);
    }

    /**
     * Inicia sesión y genera un token JWT para el usuario.
     *
     * Valida las credenciales, genera un token JWT y devuelve la información
     * del usuario junto con el token. En caso de error, se devuelve una respuesta de error.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validar las credenciales de login
        $credentials = $request->only('email', 'password');

        try {
            // Intentar generar el token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401); // Credenciales incorrectas
            }
        } catch (JWTException $e) {
            // Error al generar el token
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Obtener los detalles del trabajador
        $user = JWTAuth::user();
        $trabajador = Trabajador::join('puesto_trabajador', 'trabajadores.id', '=', 'puesto_trabajador.trabajador_id')
            ->join('puestos_laborales', 'puesto_trabajador.puesto_id', '=', 'puestos_laborales.id')
            ->join('usuarios', 'trabajadores.usuario_id', '=', 'usuarios.id')
            ->select('trabajadores.nombre', 'puestos_laborales.nombre as puesto_nombre', 'usuarios.nombre as usuario_nombre', 'usuarios.email as usuario_email')
            ->where('usuarios.email', '=', $user->email)
            ->first();

        // Verificar si se encontró al trabajador y su puesto
        if (!$trabajador) {
            return response()->json(['error' => 'Trabajador o puesto no encontrado'], 404);
        }

        // Retornar el token y la información adicional del usuario
        return response()->json([
            'token' => $token,
            'usuario_nombre' => $trabajador->usuario_nombre,
            'puesto_nombre' => $trabajador->puesto_nombre,
        ]);
    }

    /**
     * Cierra la sesión del usuario actual.
     *
     * Revoca el token JWT y finaliza la sesión del usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Revocar el token de autenticación
        auth()->logout();

        // Respuesta de éxito
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * Refresca el token de acceso del usuario.
     *
     * Genera un nuevo token si el token actual está cerca de su expiración.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Obtiene el perfil del usuario autenticado.
     *
     * Devuelve los datos del usuario actualmente autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Responde con el token y la información de expiración.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,  // Token generado
            'token_type' => 'bearer',   // Tipo de token
            'expires_in' => auth()->factory()->getTTL() * 60 // Expiración en segundos
        ]);
    }
}
