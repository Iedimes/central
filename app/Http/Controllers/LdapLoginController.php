<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Container;
use Brackets\AdminAuth\Models\AdminUser;

class LdapLoginController extends Controller
{
    /**
     * Mostrar formulario de login LDAP
     */
    public function showLoginForm()
    {
        return view('ldap.login'); // Asegurate de tener esta vista
    }

    /**
     * Login vía LDAP
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        try {
            $ldap = Container::getDefaultConnection();

            // Separar dominio si viene como muvh\osemidei
            $usernameOnly = explode('\\', $username)[1] ?? $username;

            // Buscar usuario LDAP por sAMAccountName
            $ldapUser = LdapUser::where('sAMAccountName', $usernameOnly)->first();

            if (!$ldapUser) {
                return back()->withErrors(['username' => 'Usuario LDAP no encontrado']);
            }

            // Obtener DN completo del usuario
            $userDN = $ldapUser->getDn();

            // Autenticar usando DN y contraseña
            if ($ldap->auth()->attempt($userDN, $password, true)) {

                // Obtener atributos LDAP
                $email = strtolower($ldapUser->mail[0] ?? $username);
                $first_name = $ldapUser->givenname[0] ?? $username;
                $last_name  = $ldapUser->sn[0] ?? $username;

                // Crear o actualizar usuario local en admin_users
                $localUser = AdminUser::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'       => $ldapUser->cn[0] ?? $username,
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                        'password'   => bcrypt(Str::random(16)), // contraseña aleatoria, no se usa
                    ]
                );

                // Asignar el rol "Administrator" directamente en model_has_roles
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id'    => 1,
                        'model_type' => 'Brackets\AdminAuth\Models\AdminUser',
                        'model_id'   => $localUser->id,
                    ],
                    [] // No hay valores adicionales que actualizar
                );

                // Loguear en Craftable (guard admin)
                Auth::guard('admin')->login($localUser);

                // Loguear en guard LDAP
                // Auth::guard('ldap')->login($localUser);

                // Redirigir al dashboard admin
                return redirect('admin');
            } else {
                return back()->withErrors(['username' => 'Credenciales inválidas']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['username' => 'Error de conexión LDAP: ' . $e->getMessage()]);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('ldap')->logout();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/ldap/login');
    }

}
