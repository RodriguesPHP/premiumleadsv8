<?php

namespace App\Console\Commands;

use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\v8Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CampanhaSimular extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campanhas:simular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simular clientes da carteira,';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $carteira = Carteira::where('sit',1)->where('saldo_total','>',0)->where('sit_sim',0)->first();

        if(!$carteira){
            $this->info('Nenhuma cliente aguardando simulação!');
            return;
        }

        $account = ConfigAccount::where('uuid',$carteira->id_config)->first();
        $v8 = new v8Service($account);
        $reponseV8 = $v8->GetConsulta($carteira);
        var_dump($reponseV8);
        
        if($reponseV8['error']){
            $e = json_decode($reponseV8['message'],true);
            if(isset($e['error']['statusCode'])){
                $error = $e['error']['message'];
            }else{
                $error = $e['error'];
            }

            $carteira->sit_sim = 2;
            $carteira->log_sim = $error;
            $carteira->save();

            DB::table($carteira->id_campanha)->where('cpf',$carteira->cpf)->update([
                'sit_sim' => $carteira->sit_sim,
                'log_sim' => $carteira->log_sim,
                'updated_at'=>now()
            ]);
        }else{
            $carteira->saldo_total = $reponseV8['emissionAmount'];
            $carteira->saldo_lib = $reponseV8['availableBalance'];
            $carteira->sit_sim = 1;
            $carteira->payload_sim = json_encode($reponseV8);
            $carteira->log_sim = "Simulação realizada com sucesso!";
            $carteira->save();

            DB::table($carteira->id_campanha)->where('cpf',$carteira->cpf)->update([
                'saldo_total' => $carteira->saldo_total,
                'saldo_lib' => $carteira->saldo_lib,
                'sit_sim' => $carteira->sit_sim,
                'payload_sim' => $carteira->payload_sim,
                'log_sim' => $carteira->log_sim,
                'updated_at'=>now()
            ]);
        }

    }
}
