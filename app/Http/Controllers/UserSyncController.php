<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserSyncController extends Controller
{
    /**
     * Lista de sistemas isla y sus conexiones definidas en config/database.php
     */
    private $systems = [
        'Fonavis' => 'pgsqlfonavis',
        'Concurso' => 'pgsqlconcurso',
        // agregar más sistemas si es necesario
    ];

    /**
     * Verifica en qué sistemas existe el usuario
     */
    private function checkUserAcrossSystems($email)
    {
        $foundIn = [];

        foreach ($this->systems as $name => $connection) {
            $user = DB::connection($connection)
                ->table('admin_users')
                ->where('email', $email)
                ->first();

            if ($user) {
                $foundIn[$name] = $user;
            }
        }

        return $foundIn;
    }

    /**
     * Sincroniza la contraseña de Central en los sistemas isla
     */
    private function syncPassword($email, $plainPassword)
    {
        foreach ($this->systems as $name => $connection) {
            $user = DB::connection($connection)
                ->table('admin_users')
                ->where('email', $email)
                ->first();

            if ($user) {
                DB::connection($connection)
                    ->table('admin_users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($plainPassword)
                    ]);
            }
        }
    }

    /**
     * Método principal: verifica y sincroniza usando el usuario logueado en Craftable
     */
    public function checkAndSync(Request $request)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['msg' => 'Debes iniciar sesión en Central']);
        }

        $email = $user->email;

        // Opcional: si quieres sincronizar la contraseña, debes definirla
        // $plainPassword = $request->input('password'); // si la obtienes desde un formulario
        // $this->syncPassword($email, $plainPassword);

        // Verificar en otros sistemas
        $foundIn = $this->checkUserAcrossSystems($email);

        // Guardar en sesión los sistemas donde existe
        $request->session()->put('systems', array_keys($foundIn));

        return view('dashboard', ['systems' => array_keys($foundIn)]);
    }

    /**
     * Conexión dinámica a un sistema isla
     */
    public function connectToSystem(Request $request, $systemName)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!isset($this->systems[$systemName])) {
            abort(404, 'Sistema no encontrado.');
        }

        $connectionName = $this->systems[$systemName];

        // Crear configuración dinámica
        $config = DB::connection($connectionName)->getConfig();

        // Ejemplo: consultar tabla de ejemplo en sistema isla
        $data = DB::connection($connectionName)
            ->table('admin_users')
            ->where('email', $user->email)
            ->first();

        return response()->json([
            'system' => $systemName,
            'user' => $data
        ]);
    }
}
