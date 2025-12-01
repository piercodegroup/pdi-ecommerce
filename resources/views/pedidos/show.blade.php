@extends('layouts.app')

@section('title', 'Detalhes do Pedido')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-color-secondary">Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}
        </h1>
        <span class="px-3 py-1 rounded-full text-sm font-semibold 
            {{ $pedido->status == 'Entregue' ? 'bg-green-100 text-green-800' : 
               ($pedido->status == 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ $pedido->status }}
        </span>
    </div>

    <!-- Mensagem de Pontos Ganhos -->
    @if(session('pontos_ganhos'))
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <span class="text-2xl mr-3">🎉</span>
            <div>
                <h3 class="font-bold text-green-800">Parabéns!</h3>
                <p class="text-green-700">Você ganhou <strong>{{ session('pontos_ganhos') }} pontos</strong> de fidelidade com este pedido!</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Itens do Pedido -->
        <div class="lg:col-span-2 bg-white rounded-lg border p-6">
            <h2 class="text-xl font-bold text-color-secondary mb-4">Itens do Pedido</h2>

            <div class="divide-y divide-gray-200">
                @foreach($pedido->itens as $item)
                <div class="py-4 flex">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="{{ asset($item->produto->imagem) }}" alt="{{ $item->produto->nome }}"
                            class="w-full h-full object-cover rounded-lg">
                    </div>
                    <div class="ml-4 flex-grow">
                        <h3 class="text-lg font-semibold">{{ $item->produto->nome }}</h3>
                        <p class="text-gray-600">Quantidade: {{ $item->quantidade }}</p>
                        <p class="text-color-primary font-bold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 border-t pt-4 space-y-2">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>R$ {{ number_format($pedido->preco_total + $pedido->desconto_pontos, 2, ',', '.') }}</span>
                </div>
                
                @if($pedido->desconto_pontos > 0)
                <div class="flex justify-between text-green-600">
                    <span>Desconto (Pontos Fidelidade)</span>
                    <span>- R$ {{ number_format($pedido->desconto_pontos, 2, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="flex justify-between">
                    <span>Taxa de Entrega</span>
                    <span class="text-green-600">Grátis</span>
                </div>
                
                <div class="flex justify-between font-bold text-lg pt-2 border-t">
                    <span>Total</span>
                    <span class="text-color-primary">R$ {{ number_format($pedido->preco_total, 2, ',', '.') }}</span>
                </div>

                <!-- Informação de Pontos -->
                @php
                    $pontosGanhos = \App\Services\FidelidadeService::calcularPontos($pedido->preco_total);
                @endphp
                @if($pontosGanhos > 0)
                <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="flex items-center text-sm">
                        <span class="text-lg mr-2">💎</span>
                        <span class="text-purple-700">
                            Este pedido gerou <strong>{{ $pontosGanhos }} pontos</strong> para seu programa de fidelidade
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informações do Pedido -->
        <div class="bg-white rounded-lg border p-6 h-fit sticky top-4">
            <h2 class="text-xl font-bold text-color-secondary mb-4">Informações do Pedido</h2>

            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-700">Data do Pedido</h3>
                    <p>{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div>
                    <h3 class="font-medium text-gray-700">Endereço de Entrega</h3>
                    <p>{{ $pedido->endereco->logradouro }}, {{ $pedido->endereco->numero }}</p>
                    <p>{{ $pedido->endereco->bairro }}, {{ $pedido->endereco->cidade }}/{{ $pedido->endereco->estado }}
                    </p>
                    <p>CEP: {{ $pedido->endereco->cep }}</p>
                </div>

                <div>
                    <h3 class="font-medium text-gray-700">Método de Pagamento</h3>
                    <p>{{ $pedido->metodoPagamento->nome }}</p>
                    @if($pedido->cartao)
                    <p class="text-sm">Cartão terminado em {{ substr($pedido->cartao->numero, -4) }}</p>
                    @endif
                    @if($pedido->troco)
                    <p class="text-sm">Troco para R$ {{ number_format($pedido->troco, 2, ',', '.') }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="font-medium text-gray-700">Previsão de Entrega</h3>
                    <p>{{ \Carbon\Carbon::parse($pedido->data_entrega)->format('d/m/Y H:i') }}</p>
                </div>

                <!-- Pontos de Fidelidade -->
                <div class="pt-4 border-t">
                    <div class="flex items-center mb-2">
                        <span class="text-lg mr-2">💎</span>
                        <h3 class="font-medium text-gray-700">Fidelidade</h3>
                    </div>
                    @if($pedido->desconto_pontos > 0)
                    <p class="text-sm text-green-600">
                        <strong>Pontos utilizados:</strong> 
                        {{ number_format($pedido->desconto_pontos / 0.10, 0) }} pontos
                    </p>
                    @endif
                    <p class="text-sm text-purple-600">
                        <strong>Pontos ganhos:</strong> 
                        {{ \App\Services\FidelidadeService::calcularPontos($pedido->preco_total) }} pontos
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection