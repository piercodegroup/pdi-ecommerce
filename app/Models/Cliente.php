<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Cliente extends Authenticatable {

    use HasFactory;

    protected $table = 'clientes';
    protected $fillable = ['nome', 'email', 'senha', 'telefone', 'cpf'];
    protected $hidden = ['senha', 'remember_token'];

    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'cliente_id');
    }

    public function cartoes()
    {
        return $this->hasMany(Cartao::class, 'cliente_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function sacola()
    {
        return $this->hasMany(Sacola::class, 'cliente_id');
    }

    public function sacolaAtiva()
    {
        return $this->hasOne(Sacola::class, 'cliente_id')
            ->where('status', 'Em andamento')
            ->with('itens');
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function pontosFidelidade()
    {
        return $this->hasMany(PontoFidelidade::class);
    }

    public function getPontosDisponiveisAttribute()
    {
        return $this->pontosFidelidade()
                    ->disponiveis()
                    ->sum('pontos');
    }

    public function adicionarPontos($pontos, $pedido, $descricao = null)
    {
        $validade = now()->addMonths(6);

        return $this->pontosFidelidade()->create([
            'pedido_id' => $pedido->id,
            'pontos' => $pontos,
            'descricao' => $descricao ?? "Pontos do pedido #{$pedido->id}",
            'validade' => $validade
        ]);
    }

    public function utilizarPontos($pontosUtilizar)
    {
        $pontosDisponiveis = $this->pontos_disponiveis;
        
        if ($pontosUtilizar > $pontosDisponiveis) {
            throw new \Exception('Pontos insuficientes');
        }

        $pontosRestantes = $pontosUtilizar;
        $pontosUsados = [];

        $pontos = $this->pontosFidelidade()
                      ->disponiveis()
                      ->orderBy('created_at')
                      ->get();

        foreach ($pontos as $ponto) {
            if ($pontosRestantes <= 0) break;

            if ($ponto->pontos <= $pontosRestantes) {
                $ponto->update(['utilizado' => true]);
                $pontosRestantes -= $ponto->pontos;
                $pontosUsados[] = $ponto;
            } else {
                $novosPontos = $ponto->pontos - $pontosRestantes;
                
                $ponto->update(['pontos' => $novosPontos]);
                
                $this->pontosFidelidade()->create([
                    'pedido_id' => $ponto->pedido_id,
                    'pontos' => $pontosRestantes,
                    'descricao' => "Pontos utilizados do pedido #{$ponto->pedido_id}",
                    'validade' => $ponto->validade,
                    'utilizado' => true
                ]);

                $pontosRestantes = 0;
                break;
            }
        }

        return $pontosUsados;
    }

}