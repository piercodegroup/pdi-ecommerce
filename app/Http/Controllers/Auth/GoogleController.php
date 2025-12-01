<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $cliente = Cliente::where('email', $googleUser->getEmail())->first();

            if (!$cliente) {
                $cliente = Cliente::create([
                    'nome'     => $googleUser->getName(),
                    'email'    => $googleUser->getEmail(),
                    'senha'    => null,
                    'cpf'      => null,
                    'telefone' => null,
                ]);
            }

            Auth::guard('cliente')->login($cliente);

            if (is_null($cliente->cpf) || is_null($cliente->telefone)) {
                return redirect()->route('perfil.index')->with('info', 'Complete seu cadastro para continuar');
            }

            return redirect()->route('home');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Erro ao fazer login com Google: ' . $e->getMessage()
            ]);
        }
    }
}
