@extends('layouts.app')

@section('title', 'Confirmar Pedido')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Confirmar Pedido</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Itens do Pedido -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Itens do Pedido</h2>

            <div class="divide-y divide-gray-200">
                @foreach($sacola->itens as $item)
                <div class="py-4 flex">
                    <div class="w-24 h-24 flex-shrink-0">
                        <img src="{{ asset($item->produto->imagem) }}" alt="{{ $item->produto->nome }}"
                            class="w-full h-full object-cover rounded-lg">
                    </div>
                    <div class="ml-4 flex-grow">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $item->produto->nome }}</h3>
                        <p class="text-amber-600 font-bold">R$ {{ number_format($item->produto->preco, 2, ',', '.') }}</p>
                        <p class="text-gray-600">Quantidade: {{ $item->quantidade }}</p>
                        <p class="text-gray-600">Subtotal: R$ {{ number_format($item->subtotal, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex justify-between font-bold text-lg">
                    <span class="text-gray-800">Total</span>
                    <span class="text-amber-600">R$ {{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Informações de Entrega e Pagamento -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 h-fit sticky top-4">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Informações de Entrega e Pagamento</h2>

            <form action="{{ route('pedidos.finalizar') }}" method="POST" id="form-pedido">
                @csrf

                <!-- Seção de Pontos que Serão Ganhos -->
                @php
                    use App\Services\FidelidadeService;
                    $pontosAGanhar = FidelidadeService::calcularPontos($sacola->calcularTotal());
                @endphp
                @if($pontosAGanhar > 0)
                <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl">🎉</span>
                        <h3 class="font-bold text-green-800 ml-2">Você vai ganhar pontos!</h3>
                    </div>
                    
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Pontos com este pedido:</span>
                        <strong class="text-green-600 text-lg" id="pontos_ganhar_texto">{{ $pontosAGanhar }} pontos</strong>
                    </div>
                    
                    <div class="text-sm text-gray-600 space-y-1">
                        <div class="flex justify-between">
                            <span>Valor do pedido:</span>
                            <span id="valor_pedido_texto">R$ {{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                        </div>
                        <div class="text-green-700 font-medium">
                            • Cada ponto vale R$ 0,10 em descontos futuros
                        </div>
                        <div class="text-green-700 font-medium">
                            • Pontos válidos por 6 meses
                        </div>
                    </div>
                </div>
                @endif

                <!-- Seção de Pontos de Fidelidade Disponíveis -->
                @if(Auth::guard('cliente')->user()->pontos_disponiveis > 0)
                <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
                    <div class="flex items-center mb-3">
                        <span class="text-2xl">💎</span>
                        <h3 class="font-bold text-gray-800 ml-2">Usar Pontos de Fidelidade</h3>
                    </div>
                    
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-gray-700">Seus pontos disponíveis:</span>
                        <strong class="text-purple-600 text-lg">{{ Auth::guard('cliente')->user()->pontos_disponiveis }} pontos</strong>
                    </div>
                    
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="usar_pontos" id="usar_pontos" value="1" 
                               class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                        <label for="usar_pontos" class="ml-2 text-gray-700 font-medium">
                            Quero usar meus pontos
                        </label>
                    </div>
                    
                    <div id="info_pontos" class="text-sm text-gray-600 space-y-3 hidden">
                        <!-- Seleção de Quantidade de Pontos -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Quantos pontos usar?</label>
                            <div class="flex space-x-2 mb-2">
                                <button type="button" class="pontos-rapido flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded-lg text-sm transition duration-200" data-pontos="{{ floor(Auth::guard('cliente')->user()->pontos_disponiveis * 0.25) }}">
                                    25% dos pontos
                                </button>
                                <button type="button" class="pontos-rapido flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded-lg text-sm transition duration-200" data-pontos="{{ floor(Auth::guard('cliente')->user()->pontos_disponiveis * 0.5) }}">
                                    50% dos pontos
                                </button>
                                <button type="button" class="pontos-rapido flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded-lg text-sm transition duration-200" data-pontos="{{ Auth::guard('cliente')->user()->pontos_disponiveis }}">
                                    100% dos pontos
                                </button>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input type="number" 
                                       name="pontos_usar" 
                                       id="pontos_usar"
                                       min="0" 
                                       max="{{ Auth::guard('cliente')->user()->pontos_disponiveis }}"
                                       step="1"
                                       class="flex-1 border border-gray-300 rounded-lg p-2 text-center focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                       placeholder="0"
                                       value="0">
                                <span class="text-gray-600 whitespace-nowrap">pontos</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Máximo: {{ Auth::guard('cliente')->user()->pontos_disponiveis }} pontos
                            </div>
                        </div>

                        <!-- Resumo do Uso de Pontos -->
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span>Pontos a usar:</span>
                                    <strong id="pontos_utilizados_display">0 pontos</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>Valor do desconto:</span>
                                    <strong class="text-green-600" id="valor_desconto_display">R$ 0,00</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>Pontos restantes:</span>
                                    <span id="pontos_restantes_display">{{ Auth::guard('cliente')->user()->pontos_disponiveis }} pontos</span>
                                </div>
                            </div>
                        </div>

                        <!-- Aviso sobre Pontos Ganhos -->
                        <div class="text-orange-600 font-medium hidden" id="aviso_pontos_ganhos">
                            ⚠️ Usar pontos reduz os pontos que você vai ganhar
                        </div>
                    </div>
                </div>
                @endif

                <!-- Endereço de Entrega -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Endereço de Entrega</label>
                    @foreach($enderecos as $endereco)
                    <div class="flex items-center mb-2">
                        <input type="radio" name="endereco_id" id="endereco_{{ $endereco->id }}"
                            value="{{ $endereco->id }}" class="mr-2 text-amber-600 focus:ring-amber-500" {{ $loop->first ? 'checked' : '' }}>
                        <label for="endereco_{{ $endereco->id }}" class="text-gray-700">
                            {{ $endereco->logradouro }}, {{ $endereco->numero }} - {{ $endereco->bairro }},
                            {{ $endereco->cidade }}/{{ $endereco->estado }}
                        </label>
                    </div>
                    @endforeach
                    <a href="{{ route('perfil.enderecos') }}"
                        class="text-amber-600 text-sm hover:underline">Gerenciar endereços</a>
                </div>

                <!-- Método de Pagamento -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Método de Pagamento</label>
                    <select name="metodo_pagamento_id" id="metodo_pagamento" class="w-full border border-gray-300 rounded-lg p-2 mb-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                        @foreach($metodosPagamento as $metodo)
                        <option value="{{ $metodo->id }}">{{ $metodo->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cartão de Crédito (API Fake) -->
                <div class="mb-6 hidden" id="cartao_container">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="font-bold text-gray-800 mb-3">Pagamento com Cartão</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">Número do Cartão</label>
                                <input type="text" name="dados_cartao[numero]" 
                                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                                       placeholder="1234 5678 9012 3456"
                                       maxlength="19"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">Nome do Titular</label>
                                <input type="text" name="dados_cartao[nome]" 
                                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                                       placeholder="Como no cartão"
                                       required>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Validade</label>
                                    <div class="flex space-x-2">
                                        <select name="dados_cartao[validade_mes]" class="flex-1 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                                            <option value="">Mês</option>
                                            @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                            </option>
                                            @endfor
                                        </select>
                                        <select name="dados_cartao[validade_ano]" class="flex-1 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                                            <option value="">Ano</option>
                                            @for($i = date('y'); $i <= date('y') + 10; $i++)
                                            <option value="{{ $i }}">20{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-1">CVV</label>
                                    <input type="text" name="dados_cartao[cvv]" 
                                           class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" 
                                           placeholder="123"
                                           maxlength="4"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">Parcelas</label>
                                <select name="dados_cartao[parcelas]" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" required>
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ $i }}x de R$ {{ number_format($sacola->calcularTotal() / $i, 2, ',', '.') }} Sem Juros</option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div class="items-center hidden">
                                <input type="checkbox" name="salvar_cartao" id="salvar_cartao" value="1" class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                            </div>

                            <div class="flex items-center justify-center p-3 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-green-700 font-medium">Pagamento 100% seguro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Troco para Dinheiro -->
                <div class="mb-6 hidden" id="troco_container">
                    <label class="block text-gray-700 font-medium mb-2">Troco para</label>
                    <input type="number" name="troco" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        placeholder="Informe o valor para troco" min="{{ $sacola->calcularTotal() }}" step="0.01">
                </div>

                <!-- Resumo Final -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-3">Resumo do Pedido</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="text-gray-800">R$ {{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxa de entrega:</span>
                            <span class="text-green-600">Grátis</span>
                        </div>
                        <div id="desconto_container" class="flex justify-between text-green-600 hidden">
                            <span>Desconto (pontos):</span>
                            <span>- R$ <span id="desconto_valor">0,00</span></span>
                        </div>
                        
                        @if($pontosAGanhar > 0)
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="text-sm text-gray-600">Pontos que você vai ganhar:</span>
                            <span class="text-green-600 font-semibold flex items-center">
                                <span class="mr-1" id="pontos_resumo">{{ $pontosAGanhar }}</span>
                                <span class="text-xs">pontos</span>
                            </span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-200">
                            <span class="text-gray-800">Total:</span>
                            <span class="text-amber-600">R$ 
                                <span id="total_final">{{ number_format($sacola->calcularTotal(), 2, ',', '.') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-lg hover:bg-amber-700 transition duration-200 font-semibold shadow-md" id="btn-finalizar">
                    Finalizar Pedido
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Processamento -->
<div id="modal-processamento" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Processando Pagamento</h3>
            <p class="text-gray-600 mb-4">Estamos processando seu pagamento. Isso pode levar alguns segundos.</p>
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalBase = {{ $sacola->calcularTotal() }};
    const pontosDisponiveis = {{ Auth::guard('cliente')->user()->pontos_disponiveis }};
    const pontosAGanharOriginal = {{ $pontosAGanhar }};
    
    // Elementos dos pontos
    const checkboxPontos = document.getElementById('usar_pontos');
    const infoPontos = document.getElementById('info_pontos');
    const inputPontosUsar = document.getElementById('pontos_usar');
    const descontoContainer = document.getElementById('desconto_container');
    const descontoValor = document.getElementById('desconto_valor');
    const totalFinal = document.getElementById('total_final');
    const pontosUtilizadosDisplay = document.getElementById('pontos_utilizados_display');
    const valorDescontoDisplay = document.getElementById('valor_desconto_display');
    const pontosRestantesDisplay = document.getElementById('pontos_restantes_display');
    const avisoPontosGanhos = document.getElementById('aviso_pontos_ganhos');
    const pontosGanharTexto = document.getElementById('pontos_ganhar_texto');
    const valorPedidoTexto = document.getElementById('valor_pedido_texto');
    const pontosResumo = document.getElementById('pontos_resumo');

    const metodoPagamento = document.getElementById('metodo_pagamento');
    const cartaoContainer = document.getElementById('cartao_container');
    const trocoContainer = document.getElementById('troco_container');
    const formPedido = document.getElementById('form-pedido');
    const modalProcessamento = document.getElementById('modal-processamento');
    const btnFinalizar = document.getElementById('btn-finalizar');

    metodoPagamento.addEventListener('change', function() {
        const metodo = this.value;
        
        cartaoContainer.classList.add('hidden');
        trocoContainer.classList.add('hidden');

        const camposCartao = cartaoContainer.querySelectorAll('input, select');

        camposCartao.forEach(campo => {
            if (metodo == 1) {
                cartaoContainer.classList.remove('hidden');
                campo.disabled = false;
            } else {
                campo.disabled = true;
            }
        });

        if (metodo == 2) {
            trocoContainer.classList.remove('hidden');
        }
    });

    const inputNumeroCartao = document.querySelector('input[name="dados_cartao[numero]"]');
    if (inputNumeroCartao) {
        inputNumeroCartao.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);
        });
    }

    formPedido.addEventListener('submit', function(e) {
        const metodo = metodoPagamento.value;
        
        if (metodo == 1) {
            const numeroCartao = document.querySelector('input[name="dados_cartao[numero]"]').value.replace(/\s/g, '');
            const nomeTitular = document.querySelector('input[name="dados_cartao[nome]"]').value;
            const validadeMes = document.querySelector('select[name="dados_cartao[validade_mes]"]').value;
            const validadeAno = document.querySelector('select[name="dados_cartao[validade_ano]"]').value;
            const cvv = document.querySelector('input[name="dados_cartao[cvv]"]').value;

            if (!numeroCartao || numeroCartao.length < 16 || 
                !nomeTitular || 
                !validadeMes || !validadeAno || 
                !cvv || cvv.length < 3) {
                e.preventDefault();
                alert('Por favor, preencha todos os dados do cartão corretamente.');
                return;
            }

            e.preventDefault();
            modalProcessamento.classList.remove('hidden');
            btnFinalizar.disabled = true;
            
            setTimeout(() => {
                formPedido.submit();
            }, 3000);
        }
    });

    if (checkboxPontos) {
        checkboxPontos.addEventListener('change', function() {
            if (this.checked) {
                infoPontos.classList.remove('hidden');
                descontoContainer.classList.remove('hidden');
                calcularDesconto();
            } else {
                infoPontos.classList.add('hidden');
                descontoContainer.classList.add('hidden');
                inputPontosUsar.value = 0;
                resetarTotal();
            }
        });
    }

    document.querySelectorAll('.pontos-rapido').forEach(button => {
        button.addEventListener('click', function() {
            const pontos = parseInt(this.getAttribute('data-pontos'));
            inputPontosUsar.value = pontos;
            calcularDesconto();
        });
    });

    if (inputPontosUsar) {
        inputPontosUsar.addEventListener('input', function() {
            calcularDesconto();
        });
        
        inputPontosUsar.addEventListener('change', function() {
            let valor = parseInt(this.value) || 0;
            if (valor < 0) valor = 0;
            if (valor > pontosDisponiveis) valor = pontosDisponiveis;
            this.value = valor;
            calcularDesconto();
        });
    }

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

    function calcularDesconto() {
        const pontosUsar = parseInt(inputPontosUsar.value) || 0;
        const desconto = Math.min(pontosUsar * 0.10, totalBase);
        const totalComDesconto = totalBase - desconto;
        
        const novosPontosAGanhar = calcularPontos(totalComDesconto);

        descontoValor.textContent = desconto.toFixed(2).replace('.', ',');
        totalFinal.textContent = totalComDesconto.toFixed(2).replace('.', ',');
        pontosUtilizadosDisplay.textContent = `${pontosUsar} pontos`;
        valorDescontoDisplay.textContent = `R$ ${desconto.toFixed(2).replace('.', ',')}`;
        pontosRestantesDisplay.textContent = `${pontosDisponiveis - pontosUsar} pontos`;
        
        atualizarPontosAGanhar(totalComDesconto, novosPontosAGanhar, pontosUsar);
    }

    function resetarTotal() {
        totalFinal.textContent = totalBase.toFixed(2).replace('.', ',');
        atualizarPontosAGanhar(totalBase, pontosAGanharOriginal, 0);
    }

    function atualizarPontosAGanhar(valorTotal, pontosGanhar, pontosUsados) {
        if (pontosGanharTexto) {
            pontosGanharTexto.textContent = `${pontosGanhar} pontos`;
            
            if (pontosGanhar < pontosAGanharOriginal) {
                pontosGanharTexto.className = 'text-orange-600 text-lg';
            } else {
                pontosGanharTexto.className = 'text-green-600 text-lg';
            }
        }
        
        if (valorPedidoTexto) {
            valorPedidoTexto.textContent = `R$ ${valorTotal.toFixed(2).replace('.', ',')}`;
        }
        
        if (pontosResumo) {
            pontosResumo.textContent = pontosGanhar;
            
            if (pontosGanhar < pontosAGanharOriginal) {
                pontosResumo.parentElement.className = 'text-orange-600 font-semibold flex items-center';
            } else {
                pontosResumo.parentElement.className = 'text-green-600 font-semibold flex items-center';
            }
        }
        
        if (avisoPontosGanhos) {
            if (pontosUsados > 0 && pontosGanhar < pontosAGanharOriginal) {
                avisoPontosGanhos.classList.remove('hidden');
            } else {
                avisoPontosGanhos.classList.add('hidden');
            }
        }
    }

    if (pontosGanharTexto) {
        atualizarPontosAGanhar(totalBase, pontosAGanharOriginal, 0);
    }

    metodoPagamento.dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection