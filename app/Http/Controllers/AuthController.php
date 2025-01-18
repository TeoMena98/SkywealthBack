<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario.
     *
     * Valida los datos del request, crea un nuevo usuario y guarda su información
     * en la base de datos. Luego, retorna una respuesta de éxito.
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

        // Creación del nuevo usuario en la base de datos
        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Contraseña cifrada
        ]);

        // Respuesta de éxito
        return response()->json(['message' => 'Usuario registrado correctamente'], 201);
    }

    /**
     * Inicia sesión y genera un token de acceso para el usuario.
     *
     * Valida las credenciales proporcionadas y, si son correctas, genera un token
     * JWT para autenticar al usuario. Si las credenciales son incorrectas o
     * ocurre un error al generar el token, se devuelve una respuesta con un error.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validación de las credenciales (email y contraseña)
        $credentials = $request->only('email', 'password');

        try {
            // Intentamos crear el token JWT
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401); // Credenciales incorrectas
            }
        } catch (JWTException $e) {
            // Error al generar el token
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Retornamos el token JWT
        return response()->json(compact('token'));
    }

    /**
     * Cierra la sesión del usuario actual.
     *
     * Revoca el token de acceso y cierra la sesión del usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Cierre de sesión
        auth()->logout();

        // Respuesta de éxito
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * Refresca el token de acceso.
     *
     * Si el token actual está cerca de su expiración, se genera uno nuevo.
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
     * Devuelve los datos del usuario que está actualmente autenticado.
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
            'access_token' => $token,   // Token generado
            'token_type' => 'bearer',    // Tipo de token (Bearer)
            'expires_in' => auth()->factory()->getTTL() * 60 // Tiempo de expiración en segundos
        ]);
    }
}
