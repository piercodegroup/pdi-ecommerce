@extends('layouts.app')

@section('title', 'Minha Sacola')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-color-secondary mb-8">Minha Sacola</h1>

    @if($sacola && $sacola->itens->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-lg border p-6">
            <div class="divide-y divide-gray-200">
                @foreach($sacola->itens as $item)
                <div class="py-4 flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-24 h-24 flex-shrink-0">
                        <img src="{{ asset($item->produto->imagem) }}" alt="{{ $item->produto->nome }}"
                            class="w-full h-full object-cover rounded-lg">
                    </div>

                    <div class="flex-grow">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-color-secondary">{{ $item->produto->nome }}
                                </h3>
                                <p class="text-color-primary font-bold mt-1 produto-preco" data-preco="{{ $item->produto->preco }}">
                                    R$ {{ number_format($item->produto->preco, 2, ',', '.') }}
                                </p>
                            </div>
                            <form action="{{ route('sacola.remover', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <i class='bx bx-trash text-xl'></i>
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 flex items-center">
                            <form action="{{ route('sacola.atualizar', $item->id) }}" method="POST"
                                class="flex items-center">
                                @csrf
                                @method('PATCH')
                                <button type="button" onclick="decrementQuantity(this)"
                                    class="bg-gray-200 px-3 py-1 rounded-l-lg text-color-secondary hover:bg-gray-300">
                                    <i class='bx bx-minus'></i>
                                </button>
                                <input type="number" name="quantidade" value="{{ $item->quantidade }}" min="1" max="99"
                                    class="w-16 text-center border-t border-b border-gray-200 py-1 quantity-input"
                                    data-item-id="{{ $item->id }}">
                                <button type="button" onclick="incrementQuantity(this)"
                                    class="bg-gray-200 px-3 py-1 rounded-r-lg text-color-secondary hover:bg-gray-300">
                                    <i class='bx bx-plus'></i>
                                </button>
                                <button type="submit"
                                    class="ml-4 text-sm text-color-primary hover:underline">Atualizar</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg border p-6 h-fit sticky top-4">
            <h2 class="text-xl font-bold text-color-secondary mb-4">Resumo do Pedido</h2>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="subtotal-display">R$ {{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Taxa de Entrega</span>
                    <span class="text-green-600">Grátis</span>
                </div>
                
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Pontos que você vai ganhar:</span>
                        <span class="text-green-600 font-semibold flex items-center">
                            <span class="mr-1" id="pontos-ganhos">{{ $pontosAGanhar }}</span>
                            <span class="text-xs">pontos</span>
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mb-3">
                        💡 Cada ponto vale R$ 0,10 em descontos futuros
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-color-primary">R$
                            <span id="total-pedido">{{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <span class="text-blue-500 mr-2">💎</span>
                    <div class="text-sm text-blue-700">
                        <strong>Programa de Fidelidade</strong>
                        <div class="mt-1 text-xs">
                            Faixas: R$10-15 (5pts) • R$15-30 (10pts) • R$30-50 (15pts) • R$50-100 (25pts) • +R$100 (50pts)
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('pedidos.confirmar') }}"
                class="mt-6 block w-full bg-color-primary text-white text-center py-3 rounded-lg hover:bg-opacity-90 transition font-semibold">
                Finalizar Pedido
            </a>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg border p-8 text-center">
        <i class='bx bx-cart text-6xl text-gray-300 mb-4'></i>
        <h2 class="text-xl font-semibold text-gray-600 mb-2">Sua sacola está vazia</h2>
        <p class="text-gray-500 mb-6">Adicione produtos para continuar</p>
        <a href="{{ route('home') }}"
            class="inline-block bg-color-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
            Voltar às compras
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function calcularPontos(valorTotal) {
    if (valorTotal >= 10 && valorTotal < 15) {
        return 5;
    } else if (valorTotal >= 15 && valorTotal < 30) {
        return 10;
    } else if (valorTotal >= 30 && valorTotal < 50) {
        return 15;
    } else if (valorTotal >= 50 && valorTotal < 100) {
        return 25;
    } else if (valorTotal >= 100) {
        return 50;
    }
    return 0;
}

function formatarMoeda(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',');
}

function extrairPreco(precoTexto) {
    return parseFloat(precoTexto.replace('R$', '').replace('.', '').replace(',', '.').trim());
}

function atualizarResumo() {
    let total = 0;
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        const quantidade = parseInt(input.value) || 0;
        const precoElement = input.closest('.flex-grow').querySelector('.produto-preco');
        const precoTexto = precoElement.textContent;
        const preco = extrairPreco(precoTexto);
        
        if (!isNaN(preco) && !isNaN(quantidade)) {
            total += quantidade * preco;
        }
    });

    document.getElementById('subtotal-display').textContent = formatarMoeda(total);
    document.getElementById('total-pedido').textContent = formatarMoeda(total);
    
    const pontos = calcularPontos(total);
    document.getElementById('pontos-ganhos').textContent = pontos;
    
    const pontosElement = document.getElementById('pontos-ganhos');
    if (pontos >= 25) {
        pontosElement.className = 'text-green-600 font-bold text-lg mr-1';
    } else if (pontos >= 10) {
        pontosElement.className = 'text-green-600 font-semibold mr-1';
    } else {
        pontosElement.className = 'text-green-600 mr-1';
    }
}

function incrementQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    const currentValue = parseInt(input.value) || 0;
    if (currentValue < 99) {
        input.value = currentValue + 1;
        atualizarResumo();
    }
}

function decrementQuantity(button) {
    const input = button.parentElement.querySelector('.quantity-input');
    const currentValue = parseInt(input.value) || 0;
    if (currentValue > 1) {
        input.value = currentValue - 1;
        atualizarResumo();
    }
}

document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        let value = parseInt(this.value) || 1;
        if (value < 1) value = 1;
        if (value > 99) value = 99;
        this.value = value;
        atualizarResumo();
        
        setTimeout(() => {
            this.form.submit();
        }, 1000);
    });

    input.addEventListener('input', function() {
        atualizarResumo();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('Página carregada, inicializando resumo...');
    atualizarResumo();
});

if (typeof Livewire !== 'undefined') {
    Livewire.hook('message.processed', () => {
        setTimeout(atualizarResumo, 100);
    });
}
</script>
@endpush
@endsection