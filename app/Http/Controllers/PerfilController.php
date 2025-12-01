<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Endereco;
use App\Models\Cartao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $cliente = Auth::guard('cliente')->user();
        return view('perfil.index', compact('cliente'));
    }

    public function atualizar(Request $request)
    {
        $cliente = Auth::guard('cliente')->user();

        $cpfLimpo = $request->cpf ? preg_replace('/[^0-9]/', '', $request->cpf) : null;

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clientes,email,' . $cliente->id,
            'telefone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:clientes,cpf,' . $cliente->id, // Aumentei para 14 caracteres
            'senha_atual' => 'nullable|string',
            'nova_senha' => 'nullable|string|min:8|confirmed',
        ]);

        $cliente->nome = $request->nome;
        $cliente->email = $request->email;
        $cliente->telefone = $request->telefone;

        if ($request->filled('cpf')) {
            if (is_null($cliente->senha) || is_null($cliente->cpf)) {
                $cliente->cpf = $cpfLimpo;
                \Log::debug('CPF atualizado', ['novo_cpf' => $cpfLimpo]);
            } else {
                \Log::debug('CPF não atualizado - usuário não social com CPF já definido');
            }
        }

        if ($request->filled('nova_senha')) {
            if (is_null($cliente->senha)) {
                $cliente->senha = Hash::make($request->nova_senha);
                \Log::debug('Senha definida para usuário social');
            } else {
                if (!Hash::check($request->senha_atual, $cliente->senha)) {
                    \Log::debug('Senha atual incorreta');
                    return back()->withErrors(['senha_atual' => 'Senha atual incorreta']);
                }
                $cliente->senha = Hash::make($request->nova_senha);
                \Log::debug('Senha atualizada para usuário regular');
            }
        }

        $cliente->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function enderecos()
    {
        $enderecos = Auth::guard('cliente')->user()->enderecos;
        return view('perfil.enderecos', compact('enderecos'));
    }

    public function adicionarEndereco(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:Residencial,Comercial',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'cep' => 'required|string|max:10',
        ]);

        Auth::guard('cliente')->user()->enderecos()->create($request->all());

        return back()->with('success', 'Endereço adicionado com sucesso!');
    }

    public function editarEndereco(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|in:Residencial,Comercial',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'cep' => 'required|string|max:10',
        ]);

        $endereco = Endereco::where('cliente_id', Auth::guard('cliente')->id())
                           ->findOrFail($id);
        $endereco->update($request->all());

        return back()->with('success', 'Endereço atualizado com sucesso!');
    }

    public function removerEndereco($id)
    {
        $endereco = Endereco::where('cliente_id', Auth::guard('cliente')->id())
                           ->findOrFail($id);
        $endereco->delete();

        return back()->with('success', 'Endereço removido com sucesso!');
    }











    














    public function pontos()
    {
        $cliente = Auth::guard('cliente')->user();
        $pontos = $cliente->pontosFidelidade()
                        ->with('pedido')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        return view('perfil.pontos', compact('cliente', 'pontos'));
    }

}