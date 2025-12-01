<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PontoFidelidade extends Model
{
    use HasFactory;

    protected $table = 'pontos_fidelidade';
    
    protected $fillable = [
        'cliente_id',
        'pedido_id',
        'pontos',
        'descricao',
        'validade',
        'utilizado'
    ];

    protected $casts = [
        'validade' => 'datetime',
        'utilizado' => 'boolean'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function scopeDisponiveis($query)
    {
        return $query->where('utilizado', false)
                    ->where(function($q) {
                        $q->where('validade', '>', now())
                          ->orWhereNull('validade');
                    });
    }
}