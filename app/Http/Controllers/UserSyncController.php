<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSyncController extends Controller
{
    private $systems = [
        'Fonavis' => 'pgsqlfonavis',
        'Concurso' => 'pgsqlconcurso',
    ];

    private function checkUserAcrossSystems($email)
    {
        $foundIn = [];
        foreach ($this->systems as $name => $connection) {
            $user = DB::connection($connection)->table('admin_users')->where('email', $email)->first();
            if ($user) $foundIn[$name] = $user;
        }
        return $foundIn;

    }



    /**
     * Genera token seguro de login automático en sistemas isla
     */
    private function generateLoginToken($email)
    {
        $tokens = [];
        foreach ($this->systems as $name => $connection) {
            $user = DB::connection($connection)->table('admin_users')->where('email', $email)->first();
            if ($user) {
                $token = Str::random(60);
                $expire = Carbon::now()->addMinutes(5); // token válido 5 minutos
                DB::connection($connection)->table('admin_users')->where('id', $user->id)
                    ->update([
                        'login_token' => $token,
                        'login_token_expire_at' => $expire
                    ]);
                $tokens[$name] = $token;
            }
        }
        return $tokens;
    }

    public function checkAndSync(Request $request)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) return redirect()->route('admin.login');

        $email = $user->email;

        // 2️⃣ verificamos en qué sistemas existe
        $foundIn = $this->checkUserAcrossSystems($email);

        // 3️⃣ generamos tokens de login automático
        $tokens = $this->generateLoginToken($email);

        // guardamos tokens en sesión
        $request->session()->put('login_tokens', $tokens);

        return view('dashboard', [
            'systems' => array_keys($foundIn),
            'tokens' => $tokens
        ]);
    }

    /**
     * Redirección a sistema isla usando token
     */
    public function redirectToSystem(Request $request, $systemName)
    {
        $user = Auth::guard('admin')->user();
        if (!$user) return redirect()->route('admin.login');

        $token = session("login_tokens.$systemName");
        if (!$token) abort(403, 'No token disponible');

        $url = match($systemName) {
            'Fonavis' => "http://127.0.0.1:8001/admin/login-auto?token=$token",
            'Concurso' => "http://127.0.0.1:8002/admin/login-auto?token=$token",
            default => abort(404)
        };


        return redirect($url);
    }
}
