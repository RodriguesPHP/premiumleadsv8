<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;


class CarteiraW extends Model
{
    protected $fillable = [
        'id',
        'uuid',
        'id_campanha',
        'cpf',
        'nome',
        'nasc',
        'telefone',
        'saldo_total',
        'saldo_lib',
        'payload_sim',
        'sit',
        'sit_sim',
        'sit_prop',
        'log',
        'log_sim',
        'log_prop',
        'id_proposal',
        'qnt_proposta',
        'id_config',
        'created_at',
        'updated_at'
    ];

    public function CMinsert($cliente){
        $dados = [
            'uuid' => Uuid::uuid4()->toString(),
            'id_campanha'=> 'carteira_w_s',
            'cpf'=> $cliente->cpf,
            'nome'=> $cliente->nome,
            'nasc'=> $cliente->nasc,
            'telefone'=> $cliente->telefone,
            'id_config'=>'',
            'created_at'=>now()
        ];
        $this->insert($dados);
    }
}
