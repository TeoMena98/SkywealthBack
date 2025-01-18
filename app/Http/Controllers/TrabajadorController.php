<?php

namespace App\Http\Controllers;

use App\Models\PuestoLaboral;
use App\Models\Trabajador;
use App\Models\Usuario;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrabajadorController extends Controller
{
    /**
     * Almacena un nuevo trabajador en la base de datos.
     *
     * Valida los datos del request, verifica si el trabajador ya existe, y si no
     * lo crea. Además, asigna un puesto laboral al trabajador.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validación de los datos de entrada
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'dni' => 'required|string|max:20',
                'fecha_nacimiento' => 'required|date',
                'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'puesto_laboral' => 'required|exists:puestos_laborales,id',
            ]);

            // Buscar si el trabajador ya existe por DNI
            $trabajador = Trabajador::where('dni', $validated['dni'])->first();

            if ($trabajador) {
                // Verificar si el puesto laboral ya está asignado al trabajador
                $puestoExistente = DB::table('puesto_trabajador')
                    ->where('trabajador_id', $trabajador->id)
                    ->where('puesto_id', $validated['puesto_laboral'])
                    ->exists();

                if ($puestoExistente) {
                    return response()->json([
                        'message' => 'Este puesto ya está asignado al trabajador.',
                    ], 200);
                }

                // Asignar el puesto laboral si no está asignado
                $trabajador->puestosLaborales()->attach($validated['puesto_laboral']);

                return response()->json([
                    'message' => 'Puesto laboral asignado al trabajador correctamente.',
                    'data' => $trabajador,
                ], 200);
            }

            // Si el trabajador no existe, generar un email único
            $email = $this->generarEmailUnico($validated['nombre'], $validated['apellidos']);

            // Crear un nuevo usuario asociado al trabajador
            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'email' => $email,
                'password' => bcrypt($validated['dni']), // Usar el DNI como contraseña cifrada
            ]);

            // Crear un nuevo trabajador
            $trabajador = Trabajador::create([
                'nombre' => $validated['nombre'],
                'apellidos' => $validated['apellidos'],
                'dni' => $validated['dni'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'foto' => $request->hasFile('foto') ? $request->file('foto')->store('fotos', 'public') : null,
                'usuario_id' => $usuario->id,
            ]);

            // Asociar el puesto laboral al trabajador
            $trabajador->puestosLaborales()->attach($validated['puesto_laboral']);

            return response()->json([
                'message' => 'Trabajador creado correctamente.',
                'data' => $trabajador,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Los datos proporcionados no son válidos.',
                'details' => $e->errors(), // Detalles específicos de los errores de validación
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error al procesar la solicitud.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza los datos de un trabajador.
     *
     * Este método permite modificar la información del trabajador, incluyendo su
     * foto y el puesto laboral asignado.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Buscar el trabajador por ID
        $trabajador = Trabajador::find($id);
        if (!$trabajador) {
            return response()->json(['message' => 'Trabajador no encontrado'], 404);
        }

        // Actualizar los campos del trabajador
        $trabajador->nombre = $request->input('nombre');
        $trabajador->apellidos = $request->input('apellidos');
        $trabajador->dni = $request->input('dni');
        $trabajador->fecha_nacimiento = $request->input('fecha_nacimiento');
        
        // Asociar puesto laboral
        $trabajador->puestosLaborales()->attach($request->input('puesto_laboral'));

        // Subir foto si se proporciona
        if ($request->hasFile('foto')) {
            $trabajador->foto = $request->file('foto')->store('public/fotos');
        }

        // Guardar los cambios
        $trabajador->save();

        return response()->json(['message' => 'Trabajador actualizado correctamente']);
    }

    /**
     * Obtiene todos los puestos laborales.
     *
     * Recupera y devuelve una lista de todos los puestos laborales disponibles
     * en la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function PuestosLaborales()
    {
        try {
            // Recuperar todos los puestos laborales
            $puestos = PuestoLaboral::all();

            return response()->json([
                'success' => true,
                'data' => $puestos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los puestos laborales.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene todos los trabajadores.
     *
     * Recupera y devuelve una lista de todos los trabajadores registrados
     * en la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function Trabajadores()
    {
        try {
            // Recuperar todos los trabajadores
            $trabajadores = Trabajador::all();

            return response()->json([
                'success' => true,
                'data' => $trabajadores,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los trabajadores.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Genera un email único para un trabajador.
     *
     * Utiliza el nombre y los apellidos del trabajador para crear un email
     * único y evitar duplicados en la base de datos.
     *
     * @param string $nombre
     * @param string $apellidos
     * @return string
     */
    private function generarEmailUnico($nombre, $apellidos)
    {
        $nombreBase = strtolower($nombre);
        $apellidosBase = strtolower($apellidos);
        $indice = 1;

        // Crear un email base
        $email = $nombreBase . substr($apellidosBase, 0, 1) . '@test.com';

        // Generar un email único si ya existe
        while (Usuario::where('email', $email)->exists()) {
            if ($indice < strlen($apellidosBase)) {
                $email = $nombreBase . substr($apellidosBase, 0, $indice + 1) . '@test.com';
                $indice++;
            } else {
                $email = $nombreBase . $apellidosBase . $indice . '@test.com';
                $indice++;
            }
        }

        return $email;
    }
}
