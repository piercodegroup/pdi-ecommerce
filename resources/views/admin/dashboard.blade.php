@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="p-6">

<div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-700">Dashboard</h1>
        <form method="GET" class="flex items-center space-x-4">
            <select name="periodo" onchange="this.form.submit()" 
                    class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500">
                <option value="hoje" {{ $periodo == 'hoje' ? 'selected' : '' }}>Hoje</option>
                <option value="7dias" {{ $periodo == '7dias' ? 'selected' : '' }}>Últimos 7 dias</option>
                <option value="30dias" {{ $periodo == '30dias' ? 'selected' : '' }}>Últimos 30 dias</option>
                <option value="3meses" {{ $periodo == '3meses' ? 'selected' : '' }}>Últimos 3 meses</option>
                <option value="6meses" {{ $periodo == '6meses' ? 'selected' : '' }}>Últimos 6 meses</option>
                <option value="12meses" {{ $periodo == '12meses' ? 'selected' : '' }}>Últimos 12 meses</option>
            </select>
            <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors">
                <i class='bx bx-filter'></i> Filtrar
            </button>
        </form>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium">Total de Vendas</h3>
                    <p class="text-3xl font-bold mt-2">R$ {{ number_format($totalVendas, 2, ',', '.') }}</p>
                </div>
                <i class='bx bx-credit-card text-4xl text-amber-500 opacity-20'></i>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                <span class="{{ $metricasAdicionais['crescimento_vendas'] > 0 ? 'text-green-500' : 'text-red-500' }}">
                    <i class='bx {{ $metricasAdicionais['crescimento_vendas'] > 0 ? 'bx-trending-up' : 'bx-trending-down' }}'></i>
                    {{ number_format($metricasAdicionais['crescimento_vendas'], 1) }}%
                </span>
                vs período anterior
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium">Ticket Médio</h3>
                    <p class="text-3xl font-bold mt-2">R$ {{ number_format($estatisticasVendas['ticket_medio'], 2, ',', '.') }}</p>
                </div>
                <i class='bx bx-receipt text-4xl text-amber-500 opacity-20'></i>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Por pedido
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium">Novos Clientes</h3>
                    <p class="text-3xl font-bold mt-2">{{ $fluxoClientes['novos_clientes'] }}</p>
                </div>
                <i class='bx bx-user-plus text-4xl text-amber-500 opacity-20'></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium">Pedidos Ativos</h3>
                    <p class="text-3xl font-bold mt-2">{{ $estatisticasVendas['total_pedidos'] }}</p>
                </div>
                <i class='bx bx-package text-4xl text-amber-500 opacity-20'></i>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                {{ $statusPedidos->whereIn('status', ['Em preparo', 'A caminho'])->sum('total') }} em andamento
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6 lg:col-span-2 h-[400px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium text-slate-700">Pedidos e Vendas - Últimos 7 dias</h3>
                <div class="text-sm text-gray-500">
                    {{ Carbon\Carbon::now()->subDays(7)->format('d/m/Y') }} - {{ Carbon\Carbon::now()->format('d/m/Y') }}
                </div>
            </div>
            <canvas id="pedidosVendasChart" height="250"></canvas>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-medium mb-4 text-slate-700">Status dos Pedidos</h3>
            <div class="space-y-4">
                @foreach($statusPedidos as $status)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $status->status }}</span>
                        <span>{{ $status->total }} ({{ round(($status->total/$statusPedidos->sum('total'))*100) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" 
                             style="width: {{ ($status->total/$statusPedidos->sum('total'))*100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="font-medium mb-3 text-slate-700">Métricas Rápidas</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Horário Pico:</span>
                        <span class="font-medium">{{ $metricasAdicionais['horario_pico'] ? $metricasAdicionais['horario_pico']->hora . ':00' : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Produto Mais Vendido:</span>
                        <span class="font-medium text-right">{{ $metricasAdicionais['produto_mais_vendido']->nome ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Categoria Top:</span>
                        <span class="font-medium">{{ $metricasAdicionais['categoria_mais_vendida']->nome ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium text-slate-700">Produtos Mais Vendidos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendidos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faturado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($produtosMaisVendidos as $produto)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $produto->nome }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $produto->total_vendido }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">R$ {{ number_format($produto->total_faturado, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $produto->total_pedidos }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-medium mb-4 text-slate-700">Vendas por Método de Pagamento</h3>
            <div class="space-y-4">
                @foreach($estatisticasVendas['metodo_pagamento'] as $metodo)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $metodo->nome }}</span>
                        <span>{{ $metodo->total_pedidos }} pedidos ({{ number_format($metodo->percentual, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $metodo->percentual }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        R$ {{ number_format($metodo->total_vendas, 2, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium text-slate-700">Relatório de Fluxo de Clientes</h3>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-500">
                    <div class="text-2xl font-bold text-amber-600">{{ $fluxoClientes['novos_clientes'] }}</div>
                    <div class="text-sm text-amber-600">Novos Clientes</div>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-500">
                    <div class="text-2xl font-bold text-amber-600">{{ $fluxoClientes['clientes_ativos'] }}</div>
                    <div class="text-sm text-amber-600">Clientes Ativos</div>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-lg border border-amber-500">
                    <div class="text-2xl font-bold text-amber-600">{{ $fluxoClientes['clientes_recorrentes'] }}</div>
                    <div class="text-sm text-amber-600">Clientes Recorrentes</div>
                </div>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-medium mb-4 text-slate-700">Vendas por Dia (Últimos 15 dias)</h3>
            <div class="overflow-y-auto max-h-80">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedidos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($estatisticasVendas['vendas_por_dia'] as $venda)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($venda->data)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $venda->total_pedidos }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">R$ {{ number_format($venda->total_vendas, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">R$ {{ number_format($venda->ticket_medio, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-medium text-slate-700">Pedidos Recentes</h3>
            <a href="{{ route('admin.pedidos.index') }}" class="text-sm text-amber-500 hover:underline">
                Ver todos
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pedidosRecentes as $pedido)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $pedido->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pedido->cliente->nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            R$ {{ number_format($pedido->preco_total, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $pedido->status == 'Cancelado' ? 'bg-red-100 text-red-800' : 
                                   ($pedido->status == 'Entregue' ? 'bg-green-100 text-green-800' : 
                                   'bg-yellow-100 text-yellow-800') }}">
                                {{ $pedido->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('pedidosVendasChart').getContext('2d');
        
        const pedidosData = @json($data).map(Number);
        const vendasData = @json($dataVendas).map(Number);
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($labels),
                datasets: [
                    {
                        label: 'Pedidos',
                        data: pedidosData,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Vendas (R$)',
                        data: vendasData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y1') {
                                    label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Quantidade de Pedidos'
                        },
                        ticks: {
                            precision: 0
                        },
                        min: 0
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Valor em R$'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        min: 0
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection