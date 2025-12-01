<?php

namespace App\Http\Controllers;

use App\Models\Sacola;
use App\Models\ItensSacola;
use App\Models\Produto;
use App\Services\FidelidadeService;
use Illuminate\Http\Request;

class SacolaController extends Controller
{
    public function index()
    {
        $cliente = auth('cliente')->user();
        $sacola = $cliente->sacolaAtiva;
        
        $pontosAGanhar = 0;
        if ($sacola) {
            $pontosAGanhar = FidelidadeService::calcularPontos($sacola->calcularTotal());
        }
        
        return view('sacola.index', compact('sacola', 'pontosAGanhar'));
    }

    public function adicionar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1|max:99'
        ]);

        $cliente = auth('cliente')->user();
        $sacola = $cliente->sacolaAtiva ?? $cliente->sacola()->create(['status' => 'Em andamento']);

        $produto = Produto::find($request->produto_id);

        $item = $sacola->itens()->where('produto_id', $request->produto_id)->first();

        if ($item) {
            $novaQuantidade = $item->quantidade + $request->quantidade;
            $item->update([
                'quantidade' => $novaQuantidade,
                'subtotal' => $novaQuantidade * $produto->preco
            ]);
        } else {
            $sacola->itens()->create([
                'produto_id' => $request->produto_id,
                'quantidade' => $request->quantidade,
                'subtotal' => $request->quantidade * $produto->preco
            ]);
        }

        return back()->with('success', 'Produto adicionado à sacola!');
    }

    public function atualizar(Request $request, ItensSacola $item)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1|max:99'
        ]);

        if ($item->sacola->cliente_id !== auth('cliente')->id()) {
            abort(403);
        }

        $item->update([
            'quantidade' => $request->quantidade,
            'subtotal' => $request->quantidade * $item->produto->preco
        ]);

        return back()->with('success', 'Quantidade atualizada!');
    }

    public function remover(ItensSacola $item)
    {
        if ($item->sacola->cliente_id !== auth('cliente')->id()) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Item removido da sacola');
    }
}