<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Carteira extends Model
{
    protected $fillable = [
        'cpf',
        'nome',
        'nasc',
        'telefone'
    ];

    public function CMinsert($cliente){
        $dados = [
            'uuid' => Uuid::uuid4()->toString(),
            'id_campanha'=> $cliente->campanha,
            'user_id'=> $cliente->user_id,
            'cpf'=> $cliente->cpf,
            'nome'=> $cliente->nome,
            'nasc'=> $cliente->nasc,
            'telefone'=> $cliente->telefone,
            'id_config'=>$cliente->config,
            'created_at'=>now()
        ];
        $this->insert($dados);
    }
}
