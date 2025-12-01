<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PagamentoController extends Controller
{
    public function processarCartao(Request $request)
    {
        $request->validate([
            'numero_cartao' => 'required|string|min:16|max:19',
            'nome_titular' => 'required|string|max:255',
            'validade_mes' => 'required|numeric|between:1,12',
            'validade_ano' => 'required|numeric|min:' . date('y'),
            'cvv' => 'required|string|min:3|max:4',
            'parcelas' => 'required|numeric|min:1|max:12',
            'valor' => 'required|numeric|min:0.01',
        ]);

        sleep(3);

        $random = rand(1, 100);
        
        if ($random <= 100) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento aprovado com sucesso!',
                'dados' => [
                    'codigo_autorizacao' => 'AUTH' . rand(100000, 999999),
                    'transacao_id' => 'TXN' . rand(100000000, 999999999),
                    'bandeira' => $this->identificarBandeira($request->numero_cartao),
                    'parcelas' => $request->parcelas,
                    'valor_parcela' => round($request->valor / $request->parcelas, 2),
                ]
            ]);
        }
    }

    private function identificarBandeira($numeroCartao)
    {
        $numero = preg_replace('/\D/', '', $numeroCartao);
        
        if (preg_match('/^4/', $numero)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $numero)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $numero)) {
            return 'American Express';
        } elseif (preg_match('/^3(?:0[0-5]|[68])/', $numero)) {
            return 'Diners Club';
        } elseif (preg_match('/^6(?:011|5)/', $numero)) {
            return 'Discover';
        } else {
            return 'Outra';
        }
    }

    public function simularAntiFraude(Request $request)
    {
        sleep(2);
        
        return response()->json([
            'success' => true,
            'analise_fraude' => 'APROVADO',
            'score_risco' => rand(1, 100),
            'recomendacao' => 'Transação segura'
        ]);
    }
}