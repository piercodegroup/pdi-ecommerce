<?php

namespace App\Http\Controllers;

use App\Services\FidelidadeService;
use App\Models\Pedido;
use App\Models\Sacola;
use App\Models\Endereco;
use App\Models\MetodoPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Auth::guard('cliente')->user()->pedidos()
            ->with('itens.produto')
            ->orderBy('id', 'desc')
            ->paginate(7);

        return view('pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Auth::guard('cliente')->user()->pedidos()
            ->with(['itens.produto', 'endereco', 'metodoPagamento'])
            ->findOrFail($id);

        return view('pedidos.show', compact('pedido'));
    }

    public function confirmar()
    {
        $cliente = Auth::guard('cliente')->user();
        $sacola = $cliente->sacolaAtiva;
        
        if (!$sacola || $sacola->itens->isEmpty()) {
            return redirect()->route('sacola.index')->with('error', 'Sua sacola está vazia');
        }

        $enderecos = $cliente->enderecos;
        $metodosPagamento = MetodoPagamento::all();

        return view('pedidos.confirmar', compact(
            'sacola',
            'enderecos',
            'metodosPagamento'
        ));
    }

    public function finalizar(Request $request)
    {
        $request->validate([
            'endereco_id' => 'required|exists:enderecos,id',
            'metodo_pagamento_id' => 'required|exists:metodos_pagamento,id',
            'troco' => 'nullable|required_if:metodo_pagamento_id,2',
            'usar_pontos' => 'nullable|boolean',
            'dados_cartao.numero' => 'required_if:metodo_pagamento_id,1',
            'dados_cartao.nome' => 'required_if:metodo_pagamento_id,1',
            'dados_cartao.validade_mes' => 'required_if:metodo_pagamento_id,1',
            'dados_cartao.validade_ano' => 'required_if:metodo_pagamento_id,1',
            'dados_cartao.cvv' => 'required_if:metodo_pagamento_id,1',
            'dados_cartao.parcelas' => 'required_if:metodo_pagamento_id,1',
        ]);

        $cliente = Auth::guard('cliente')->user();
        $sacola = $cliente->sacolaAtiva;

        if (!$sacola || $sacola->itens->isEmpty()) {
            return redirect()->route('sacola.index')->with('error', 'Sua sacola está vazia');
        }

        $precoTotal = $sacola->calcularTotal();
        $descontoPontos = 0;
        $valorPonto = 0.10;

        if ($request->usar_pontos) {
            $pontosDisponiveis = $cliente->pontos_disponiveis;
            $descontoPontos = min($pontosDisponiveis * $valorPonto, $precoTotal);
            $precoTotal -= $descontoPontos;
        }

        $codigoAutorizacao = null;
        $transacaoId = null;

        if ($request->metodo_pagamento_id == 1) {
            $resultadoPagamento = $this->processarPagamentoCartao($request, $precoTotal);
            
            if (!$resultadoPagamento['success']) {
                \Log::error('Erro no pagamento: ' . $resultadoPagamento['message']);
                return back()->with('error', $resultadoPagamento['message']);
            }

            $codigoAutorizacao = $resultadoPagamento['dados']['codigo_autorizacao'] ?? null;
            $transacaoId = $resultadoPagamento['dados']['transacao_id'] ?? null;
        }

        $pedido = new Pedido();
        $pedido->cliente_id = $cliente->id;
        $pedido->endereco_id = $request->endereco_id;
        $pedido->metodo_pagamento_id = $request->metodo_pagamento_id;
        $pedido->preco_total = $precoTotal;
        $pedido->desconto_pontos = $descontoPontos;
        $pedido->troco = $request->troco ?? null;
        $pedido->codigo_autorizacao = $codigoAutorizacao;
        $pedido->transacao_id = $transacaoId;
        $pedido->status = 'Aguardando confirmação da loja';
        $pedido->data_pedido = now();
        $pedido->data_entrega = now()->addHours(2);
        $pedido->save();

        foreach ($sacola->itens as $item) {
            $pedido->itens()->create([
                'produto_id' => $item->produto_id,
                'quantidade' => $item->quantidade,
                'subtotal' => $item->subtotal,
            ]);

            $produto = $item->produto;
            $produto->estoque -= $item->quantidade;
            $produto->save();
        }

        if ($request->usar_pontos && $descontoPontos > 0) {
            $pontosUtilizar = $descontoPontos / $valorPonto;
            $cliente->utilizarPontos($pontosUtilizar);
        }

        $pontosGanhos = FidelidadeService::calcularPontos($precoTotal);
        if ($pontosGanhos > 0) {
            $cliente->adicionarPontos($pontosGanhos, $pedido);
        }

        $sacola->itens()->delete();
        $sacola->status = 'Pedido finalizado';
        $sacola->save();

        return redirect()->route('pedidos.show', $pedido->id)
            ->with('success', 'Pedido realizado com sucesso!')
            ->with('pontos_ganhos', $pontosGanhos);
    }

    private function processarPagamentoCartao(Request $request, $valorTotal)
    {
        if (!$request->dados_cartao) {
            return [
                'success' => false,
                'message' => 'Dados do cartão não encontrados.'
            ];
        }

        try {
            $pagamentoController = new PagamentoController();
            $resposta = $pagamentoController->processarCartao(new Request([
                'numero_cartao' => $request->dados_cartao['numero'],
                'nome_titular' => $request->dados_cartao['nome'],
                'validade_mes' => $request->dados_cartao['validade_mes'],
                'validade_ano' => $request->dados_cartao['validade_ano'],
                'cvv' => $request->dados_cartao['cvv'],
                'parcelas' => $request->dados_cartao['parcelas'],
                'valor' => $valorTotal,
            ]));

            return $resposta->getData(true);
        } catch (\Exception $e) {
            \Log::error('Exceção ao processar pagamento: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ];
        }
    }

}