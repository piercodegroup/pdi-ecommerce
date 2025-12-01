<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\ItensPedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', '30dias');
        $dataInicio = $this->getDataInicioPorPeriodo($periodo);
        
        $totalVendas = Pedido::where('status', '!=', 'Cancelado')
            ->when($dataInicio, function($query) use ($dataInicio) {
                return $query->where('data_pedido', '>=', $dataInicio);
            })
            ->sum('preco_total');

        $pedidos30Dias = Pedido::select(
                DB::raw('DATE(data_pedido) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(preco_total) as total_vendas')
            )
            ->where('data_pedido', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];
        $dataVendas = [];
        
        for ($i = 7; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $pedidoDia = $pedidos30Dias->firstWhere('date', $date);
            $labels[] = Carbon::now()->subDays($i)->format('d/m');
            $data[] = $pedidoDia ? $pedidoDia->total : 0;
            $dataVendas[] = $pedidoDia ? floatval($pedidoDia->total_vendas) : 0;
        }

        $statusPedidos = Pedido::select('status', DB::raw('COUNT(*) as total'))
            ->when($dataInicio, function($query) use ($dataInicio) {
                return $query->where('data_pedido', '>=', $dataInicio);
            })
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->get();

        $pedidosRecentes = Pedido::with('cliente')
            ->orderBy('data_pedido', 'desc')
            ->take(5)
            ->get();

        $estatisticasVendas = $this->getEstatisticasVendas($dataInicio);

        $produtosMaisVendidos = $this->getProdutosMaisVendidos($dataInicio);

        $fluxoClientes = $this->getFluxoClientes($dataInicio);

        $metricasAdicionais = $this->getMetricasAdicionais($dataInicio);

        return view('admin.dashboard', compact(
            'labels', 
            'data', 
            'dataVendas',
            'statusPedidos', 
            'totalVendas', 
            'pedidosRecentes',
            'estatisticasVendas',
            'produtosMaisVendidos',
            'fluxoClientes',
            'metricasAdicionais',
            'periodo'
        ));
    }

    private function getEstatisticasVendas($dataInicio = null)
    {
        $query = Pedido::where('status', '!=', 'Cancelado');

        if ($dataInicio) {
            $query->where('data_pedido', '>=', $dataInicio);
        }

        $totalPedidos = $query->count();
        $totalVendas = $query->sum('preco_total');

        return [
            'total_vendas' => $totalVendas,
            'total_pedidos' => $totalPedidos,
            'ticket_medio' => $totalPedidos > 0 ? $totalVendas / $totalPedidos : 0,
            'vendas_por_dia' => $this->getVendasPorDia($dataInicio),
            'vendas_por_mes' => $this->getVendasPorMes(),
            'metodo_pagamento' => $this->getVendasPorMetodoPagamento($dataInicio),
        ];
    }

    private function getVendasPorDia($dataInicio = null)
    {
        $query = Pedido::select(
            DB::raw('DATE(data_pedido) as data'),
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(preco_total) as total_vendas'),
            DB::raw('AVG(preco_total) as ticket_medio')
        )
        ->where('status', '!=', 'Cancelado')
        ->groupBy('data')
        ->orderBy('data', 'desc');

        if ($dataInicio) {
            $query->where('data_pedido', '>=', $dataInicio);
        }

        return $query->limit(15)->get();
    }

    private function getVendasPorMes()
    {
        return Pedido::select(
            DB::raw('YEAR(data_pedido) as ano'),
            DB::raw('MONTH(data_pedido) as mes'),
            DB::raw('COUNT(*) as total_pedidos'),
            DB::raw('SUM(preco_total) as total_vendas')
        )
        ->where('status', '!=', 'Cancelado')
        ->where('data_pedido', '>=', Carbon::now()->subMonths(12))
        ->groupBy('ano', 'mes')
        ->orderBy('ano', 'desc')
        ->orderBy('mes', 'desc')
        ->get();
    }

    private function getVendasPorMetodoPagamento($dataInicio = null)
    {
        $queryTotal = Pedido::where('status', '!=', 'Cancelado');
        if ($dataInicio) {
            $queryTotal->where('data_pedido', '>=', $dataInicio);
        }
        $totalPedidos = $queryTotal->count();

        $query = Pedido::join('metodos_pagamento', 'pedidos.metodo_pagamento_id', '=', 'metodos_pagamento.id')
            ->select(
                'metodos_pagamento.nome',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(pedidos.preco_total) as total_vendas'),
                DB::raw('(COUNT(*) * 100.0 / ' . ($totalPedidos ?: 1) . ') as percentual')
            )
            ->where('pedidos.status', '!=', 'Cancelado')
            ->groupBy('metodos_pagamento.id', 'metodos_pagamento.nome');

        if ($dataInicio) {
            $query->where('pedidos.data_pedido', '>=', $dataInicio);
        }

        return $query->get();
    }

    private function getProdutosMaisVendidos($dataInicio = null)
    {
        $query = ItensPedido::join('pedidos', 'itens_pedido.pedido_id', '=', 'pedidos.id')
            ->join('produtos', 'itens_pedido.produto_id', '=', 'produtos.id')
            ->select(
                'produtos.id',
                'produtos.nome',
                'produtos.preco',
                DB::raw('SUM(itens_pedido.quantidade) as total_vendido'),
                DB::raw('SUM(itens_pedido.subtotal) as total_faturado'),
                DB::raw('COUNT(DISTINCT pedidos.id) as total_pedidos')
            )
            ->where('pedidos.status', '!=', 'Cancelado')
            ->groupBy('produtos.id', 'produtos.nome', 'produtos.preco')
            ->orderBy('total_vendido', 'desc');

        if ($dataInicio) {
            $query->where('pedidos.data_pedido', '>=', $dataInicio);
        }

        return $query->limit(10)->get();
    }

    private function getFluxoClientes($dataInicio = null)
    {
        $queryClientes = Cliente::query();
        if ($dataInicio) {
            $queryClientes->where('created_at', '>=', $dataInicio);
        }

        $novosClientes = $queryClientes->count();

        $queryClientesAtivos = Pedido::select(DB::raw('COUNT(DISTINCT cliente_id) as total'))
            ->where('status', '!=', 'Cancelado');

        if ($dataInicio) {
            $queryClientesAtivos->where('data_pedido', '>=', $dataInicio);
        }

        $clientesAtivos = $queryClientesAtivos->first()->total;

        $frequenciaCompras = Pedido::select(
                'cliente_id',
                DB::raw('COUNT(*) as total_pedidos')
            )
            ->where('status', '!=', 'Cancelado')
            ->when($dataInicio, function($query) use ($dataInicio) {
                return $query->where('data_pedido', '>=', $dataInicio);
            })
            ->groupBy('cliente_id')
            ->get();

        return [
            'novos_clientes' => $novosClientes,
            'clientes_ativos' => $clientesAtivos,
            'total_clientes' => Cliente::count(),
            'taxa_retencao' => $this->calcularTaxaRetencao($dataInicio),
            'frequencia_media' => $frequenciaCompras->avg('total_pedidos') ?? 0,
            'clientes_recorrentes' => $frequenciaCompras->where('total_pedidos', '>', 1)->count(),
            'evolucao_novos_clientes' => $this->getEvolucaoNovosClientes(),
        ];
    }

    private function calcularTaxaRetencao($dataInicio = null)
    {
        $periodoAnterior = $dataInicio ? 
            Carbon::parse($dataInicio)->subDays(30) : 
            Carbon::now()->subDays(60);
        
        $dataFimPeriodoAnterior = $dataInicio ?? Carbon::now()->subDays(30);

        $clientesPeriodoAnterior = Pedido::where('status', '!=', 'Cancelado')
            ->where('data_pedido', '>=', $periodoAnterior)
            ->where('data_pedido', '<', $dataFimPeriodoAnterior)
            ->distinct('cliente_id')
            ->count('cliente_id');

        if ($clientesPeriodoAnterior == 0) return 0;

        $clientesRetidos = Pedido::where('status', '!=', 'Cancelado')
            ->where('data_pedido', '>=', $dataInicio ?? Carbon::now()->subDays(30))
            ->whereIn('cliente_id', function($query) use ($periodoAnterior, $dataFimPeriodoAnterior) {
                $query->select('cliente_id')
                    ->from('pedidos')
                    ->where('status', '!=', 'Cancelado')
                    ->where('data_pedido', '>=', $periodoAnterior)
                    ->where('data_pedido', '<', $dataFimPeriodoAnterior);
            })
            ->distinct('cliente_id')
            ->count('cliente_id');

        return ($clientesRetidos / $clientesPeriodoAnterior) * 100;
    }

    private function getEvolucaoNovosClientes()
    {
        return Cliente::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('data')
            ->orderBy('data')
            ->get()
            ->pluck('total', 'data')
            ->toArray();
    }

    private function getMetricasAdicionais($dataInicio = null)
    {
        return [
            'crescimento_vendas' => $this->calcularCrescimentoVendas(),
            'produto_mais_vendido' => $this->getProdutoMaisVendido($dataInicio),
            'horario_pico' => $this->getHorarioPico($dataInicio),
            'categoria_mais_vendida' => $this->getCategoriaMaisVendida($dataInicio),
        ];
    }

    private function calcularCrescimentoVendas()
    {
        $vendasAtual = Pedido::where('status', '!=', 'Cancelado')
            ->where('data_pedido', '>=', Carbon::now()->subDays(30))
            ->sum('preco_total');

        $vendasAnterior = Pedido::where('status', '!=', 'Cancelado')
            ->where('data_pedido', '>=', Carbon::now()->subDays(60))
            ->where('data_pedido', '<', Carbon::now()->subDays(30))
            ->sum('preco_total');

        if ($vendasAnterior == 0) return $vendasAtual > 0 ? 100 : 0;

        return (($vendasAtual - $vendasAnterior) / $vendasAnterior) * 100;
    }

    private function getProdutoMaisVendido($dataInicio = null)
    {
        $query = ItensPedido::join('pedidos', 'itens_pedido.pedido_id', '=', 'pedidos.id')
            ->join('produtos', 'itens_pedido.produto_id', '=', 'produtos.id')
            ->select('produtos.nome', DB::raw('SUM(itens_pedido.quantidade) as total'))
            ->where('pedidos.status', '!=', 'Cancelado')
            ->groupBy('produtos.id', 'produtos.nome')
            ->orderBy('total', 'desc');

        if ($dataInicio) {
            $query->where('pedidos.data_pedido', '>=', $dataInicio);
        }

        return $query->first();
    }

    private function getHorarioPico($dataInicio = null)
    {
        $query = Pedido::select(
                DB::raw('HOUR(data_pedido) as hora'),
                DB::raw('COUNT(*) as total_pedidos')
            )
            ->where('status', '!=', 'Cancelado')
            ->groupBy('hora')
            ->orderBy('total_pedidos', 'desc');

        if ($dataInicio) {
            $query->where('data_pedido', '>=', $dataInicio);
        }

        return $query->first();
    }

    private function getCategoriaMaisVendida($dataInicio = null)
    {
        $query = ItensPedido::join('pedidos', 'itens_pedido.pedido_id', '=', 'pedidos.id')
            ->join('produtos', 'itens_pedido.produto_id', '=', 'produtos.id')
            ->join('categorias_produto', 'produtos.categoria_id', '=', 'categorias_produto.id')
            ->select(
                'categorias_produto.nome',
                DB::raw('SUM(itens_pedido.quantidade) as total_vendido'),
                DB::raw('SUM(itens_pedido.subtotal) as total_faturado')
            )
            ->where('pedidos.status', '!=', 'Cancelado')
            ->groupBy('categorias_produto.id', 'categorias_produto.nome')
            ->orderBy('total_vendido', 'desc');

        if ($dataInicio) {
            $query->where('pedidos.data_pedido', '>=', $dataInicio);
        }

        return $query->first();
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        return match($periodo) {
            'hoje' => Carbon::today(),
            '7dias' => Carbon::now()->subDays(7),
            '30dias' => Carbon::now()->subDays(30),
            '3meses' => Carbon::now()->subMonths(3),
            '6meses' => Carbon::now()->subMonths(6),
            '12meses' => Carbon::now()->subMonths(12),
            default => Carbon::now()->subDays(30),
        };
    }

    public function exportarRelatorio(Request $request)
    {
        $tipo = $request->get('tipo');
        $periodo = $request->get('periodo', '30dias');
        $dataInicio = $this->getDataInicioPorPeriodo($periodo);

        switch ($tipo) {
            case 'vendas':
                $dados = $this->getEstatisticasVendas($dataInicio);
                break;
            case 'produtos':
                $dados = $this->getProdutosMaisVendidos($dataInicio);
                break;
            case 'clientes':
                $dados = $this->getFluxoClientes($dataInicio);
                break;
            default:
                return back()->with('error', 'Tipo de relatório inválido');
        }

        return back()->with('success', 'Relatório gerado com sucesso');
    }
}