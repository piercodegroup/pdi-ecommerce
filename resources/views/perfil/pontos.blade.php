@extends('layouts.app')

@section('title', 'Meus Pontos de Fidelidade')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-color-secondary">Meus Pontos de Fidelidade</h1>
        <div class="text-sm text-gray-600">
            <a href="{{ route('perfil') }}" class="text-color-primary hover:underline">← Voltar ao Perfil</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-purple-500 to-blue-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center mb-5">
                <h3 class="font-bold text-lg">Pontos Disponíveis</h3>
            </div>
            <div class="text-3xl font-bold mb-2">{{ $cliente->pontos_disponiveis }}</div>
            <p class="text-purple-100 text-sm">pontos para usar</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center mb-5">
                <h3 class="font-bold text-lg">Em Descontos</h3>
            </div>
            <div class="text-3xl font-bold mb-2">R$ {{ number_format($cliente->pontos_disponiveis * 0.10, 2, ',', '.') }}</div>
            <p class="text-green-100 text-sm">valor disponível</p>
        </div>

        @php
            $pontosExpirando = $cliente->pontosFidelidade()
                ->disponiveis()
                ->where('validade', '<=', now()->addDays(30))
                ->sum('pontos');
        @endphp
        <div class="bg-gradient-to-br from-orange-500 to-red-500 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center mb-5">
                <h3 class="font-bold text-lg">A Expirar</h3>
            </div>
            <div class="text-3xl font-bold mb-2">{{ $pontosExpirando }}</div>
            <p class="text-orange-100 text-sm">próximos 30 dias</p>
        </div>

        @php
            $totalGanho = $cliente->pontosFidelidade()->sum('pontos');
        @endphp
        <div class="bg-gradient-to-br from-gray-600 to-gray-800 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center mb-5">
                <h3 class="font-bold text-lg">Total Ganho</h3>
            </div>
            <div class="text-3xl font-bold mb-2">{{ $totalGanho }}</div>
            <p class="text-gray-300 text-sm">desde o início</p>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-6 mb-8">
        <h2 class="text-xl font-bold text-color-secondary mb-4">Como Funciona o Programa de Fidelidade</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-800 mb-3">🎯 Ganhe Pontos</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span><strong>R$ 10-15</strong> → 5 pontos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span><strong>R$ 15-30</strong> → 10 pontos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span><strong>R$ 30-50</strong> → 15 pontos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span><strong>R$ 50-100</strong> → 25 pontos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✓</span>
                        <span><strong>Acima de R$ 100</strong> → 50 pontos</span>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 mb-3">Use Seus Pontos</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="text-purple-500 mr-2">💎</span>
                        <span><strong>1 ponto = R$ 0,10</strong> de desconto</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-500 mr-2">⏰</span>
                        <span>Pontos válidos por <strong>6 meses</strong></span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-500 mr-2">🛒</span>
                        <span>Use na finalização do pedido</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-500 mr-2">📱</span>
                        <span>Escolha quantos pontos usar</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-color-secondary">📊 Histórico de Pontos</h2>
            <div class="flex space-x-2">
                <button class="filter-btn px-3 py-1 border rounded-lg text-sm" data-filter="all">
                    Todos
                </button>
                <button class="filter-btn px-3 py-1 border rounded-lg text-sm" data-filter="disponiveis">
                    Disponíveis
                </button>
                <button class="filter-btn px-3 py-1 border rounded-lg text-sm" data-filter="utilizados">
                    Utilizados
                </button>
                <button class="filter-btn px-3 py-1 border rounded-lg text-sm" data-filter="expirados">
                    Expirados
                </button>
            </div>
        </div>

        @if($pontos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Data</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Descrição</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Pedido</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Pontos</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Validade</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pontos as $ponto)
                    <tr class="border-b hover:bg-gray-50 ponto-item" 
                        data-status="{{ $ponto->utilizado ? 'utilizados' : ($ponto->validade && $ponto->validade->isPast() ? 'expirados' : 'disponiveis') }}">
                        <td class="py-3 px-4">
                            <div class="text-sm text-gray-600">
                                {{ $ponto->created_at->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $ponto->created_at->format('H:i') }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-sm font-medium text-gray-800">
                                {{ $ponto->descricao }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($ponto->pedido)
                            <a href="{{ route('pedidos.show', $ponto->pedido_id) }}" 
                               class="text-color-primary hover:underline text-sm">
                                #{{ str_pad($ponto->pedido_id, 6, '0', STR_PAD_LEFT) }}
                            </a>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $ponto->pontos > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $ponto->pontos > 0 ? '+' : '' }}{{ $ponto->pontos }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-sm text-gray-600">
                                {{ $ponto->validade ? $ponto->validade->format('d/m/Y') : 'Não expira' }}
                            </div>
                            @if($ponto->validade && !$ponto->utilizado)
                                @php
                                    $diasRestantes = $ponto->validade->diffInDays(now());
                                @endphp
                                @if($diasRestantes <= 7)
                                <div class="text-xs text-red-500 font-medium">
                                    ⏳ {{ $diasRestantes }} dias
                                </div>
                                @elseif($diasRestantes <= 30)
                                <div class="text-xs text-orange-500">
                                    ⏳ {{ $diasRestantes }} dias
                                </div>
                                @endif
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($ponto->utilizado)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                🎯 Utilizado
                            </span>
                            @elseif($ponto->validade && $ponto->validade->isPast())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ⏰ Expirado
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✅ Disponível
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pontos->links() }}
        </div>
        @else
        <div class="text-center py-8">
            <div class="text-6xl mb-4">💎</div>
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhum ponto registrado</h3>
            <p class="text-gray-500 mb-4">Faça seu primeiro pedido e comece a acumular pontos!</p>
            <a href="{{ route('produtos.index') }}" class="bg-color-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90">
                Fazer um Pedido
            </a>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const pontoItems = document.querySelectorAll('.ponto-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-color-primary', 'text-white');
                btn.classList.add('border', 'text-gray-700');
            });
            this.classList.add('bg-color-primary', 'text-white');
            this.classList.remove('border', 'text-gray-700');

            pontoItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'table-row';
                } else {
                    const status = item.getAttribute('data-status');
                    if (status === filter) {
                        item.style.display = 'table-row';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });

    document.querySelector('.filter-btn[data-filter="all"]').click();
});
</script>
@endpush

<style>
.ponto-item {
    transition: all 0.3s ease;
}

.filter-btn {
    transition: all 0.2s ease;
}

.filter-btn:hover {
    transform: translateY(-1px);
}
</style>