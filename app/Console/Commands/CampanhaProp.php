<?php

namespace App\Console\Commands;

use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\Models\Proposta;
use App\v8Service;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CampanhaProp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campanhas:proposta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Digitar Proposta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $carteira = Carteira::where('sit_sim',1)->where('sit_prop',0)->first();

        if(!$carteira){
            $this->info('Nenhuma cliente aguardando simulação!');
            return;
        }

        $account = ConfigAccount::where('uuid',$carteira->id_config)->first();
        $v8 = new v8Service($account);

        $payload = json_decode($carteira->payload_sim,true);

        $dados = [
            'nome'=>$carteira->nome,
            'cpf'=>$carteira->cpf,
            'fgtsSimulationId'=>$payload['id'],
            'nasc'=> DateTime::createFromFormat('d/m/Y', $carteira->nasc)->format('Y-m-d'),
            'telefone'=>substr($carteira->telefone, 2, 10),
            'ddd'=> substr($carteira->telefone, 0, 2),
            'fgtsProposalsPeriods'=>$payload['installments'],
        ];

        $responsev8 = $v8->proposta($dados);
        var_dump($responsev8);
        if($responsev8['error']){
            $e = json_decode($responsev8['message'],true);
            if(isset($e['error']['statusCode'])){
                $error = $e['error']['message'];
            }else{
                $error = $e['error'];
            }

            $carteira->sit_prop = 2;
            $carteira->log_prop = $error;
            $carteira->save();

            DB::table($carteira->id_campanha)->where('cpf',$carteira->cpf)->update([
                'sit_prop' => $carteira->sit_prop,
                'log_prop' => $carteira->log_prop,
                'updated_at'=>now()
            ]);

        }else{

            $proposta = new Proposta;
            $proposta->id_cliente = $carteira->uuid;
            $proposta->id_proposal = $responsev8['id'];
            $proposta->number_proposal = $responsev8['contractNumber'];
            $proposta->value_proposal = $carteira->saldo_lib;
            $proposta->link_proposal = $responsev8['formalizationLink'];
            $proposta->status = "pending";
            $proposta->id_config = $carteira->id_config;

            $carteira->sit_prop = 1;
            $carteira->log_prop = "Proposta gerada com sucesso!";
            $carteira->save();

            DB::table($carteira->id_campanha)->where('cpf',$carteira->cpf)->update([
                'sit_prop' => $carteira->sit_prop,
                'log_prop' => $carteira->log_prop,
                'updated_at'=>now()
            ]);

        }
        
    }
}
