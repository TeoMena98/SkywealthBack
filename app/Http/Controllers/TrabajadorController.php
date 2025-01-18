<?php

namespace App\Http\Controllers;

use App\Models\PuestoLaboral;
use App\Models\Trabajador;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'puesto_laboral' => 'required|array',
                'puesto_laboral.*' => 'exists:puestos_laborales,id',
            ]);

            // Verificar si el trabajador ya existe por DNI
            $trabajador = Trabajador::where('dni', $validated['dni'])->first();

            if ($trabajador) {
                // Verificar si alguno de los puestos laborales ya está asignado al trabajador
                foreach ($validated['puesto_laboral'] as $puesto_id) {
                    $puestoExistente = DB::table('puesto_trabajador')
                        ->where('trabajador_id', $trabajador->id)
                        ->where('puesto_id', $puesto_id)
                        ->exists();

                    if ($puestoExistente) {
                        return response()->json([
                            'message' => 'Uno de los puestos ya está asignado al trabajador.',
                        ], 200);
                    }
                }

                // Asignar los puestos laborales si no están asignados
                $trabajador->puestosLaborales()->attach($validated['puesto_laboral']);

                return response()->json([
                    'message' => 'Puestos laborales asignados al trabajador correctamente.',
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

            // Asociar los puestos laborales al trabajador
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

        // Validaciones y actualización de los campos
        $updated = false;

        // Si el nombre es diferente, actualiza
        if ($trabajador->nombre !== $request->input('nombre')) {
            $trabajador->nombre = $request->input('nombre');
            $updated = true;
        }

        // Si los apellidos son diferentes, actualiza
        if ($trabajador->apellidos !== $request->input('apellidos')) {
            $trabajador->apellidos = $request->input('apellidos');
            $updated = true;
        }

        // Si la fecha de nacimiento es diferente, actualiza
        if ($trabajador->fecha_nacimiento !== $request->input('fecha_nacimiento')) {
            $trabajador->fecha_nacimiento = $request->input('fecha_nacimiento');
            $updated = true;
        }

        // Si se cambiaron el nombre o apellidos, verificar si ya existe un trabajador con el mismo nombre y apellido
        if ($updated) {
            $existingTrabajador = Trabajador::where('nombre', $trabajador->nombre)
                ->where('apellidos', $trabajador->apellidos)
                ->first();

            if ($existingTrabajador) {
                // Si existe, generar un email único
                $email = $this->generarEmailUnico($trabajador->nombre, $trabajador->apellidos);
                $trabajador->email = $email;  // Asignar el email único al trabajador
            }
        }

        // Asociar puesto laboral si es proporcionado
        if ($request->has('puesto_laboral')) {
            $trabajador->puestosLaborales()->sync($request->input('puesto_laboral'));
        }

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
     * en la base de datos, junto con sus puestos laborales.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function Trabajadores()
    {
        try {
            $trabajadores = Trabajador::join('puesto_trabajador', 'trabajadores.id', '=', 'puesto_trabajador.trabajador_id')
                ->join('puestos_laborales', 'puesto_trabajador.puesto_id', '=', 'puestos_laborales.id')
                ->join('usuarios', 'trabajadores.usuario_id', '=', 'usuarios.id')
                ->select('trabajadores.*', 'puesto_trabajador.puesto_id', 'puestos_laborales.nombre as puesto_nombre', 'usuarios.nombre as usuario_nombre', 'usuarios.email as usuario_email')
                ->get()
                ->groupBy('id');

            // Agrupar y combinar los puestos laborales en una sola entrada por trabajador
            $trabajadores = $trabajadores->map(function ($trabajador) {
                // Obtener los nombres de los puestos como una cadena
                $puestosNombres = $trabajador->pluck('puesto_nombre')->join(', ');

                // Obtener los IDs de los puestos como un array
                $puestosId = $trabajador->pluck('puesto_id')->toArray();

                // Asignar las cadenas combinadas de puestos al trabajador
                $trabajador[0]->puestos = $puestosNombres;
                $trabajador[0]->puestosId = $puestosId;

                // Devolver el primer trabajador con los puestos agregados
                return $trabajador[0];
            });

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
