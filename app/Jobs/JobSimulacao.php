<?php

namespace App\Jobs;

use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\v8Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobSimulacao implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    public $carteiraId;

    public function __construct($carteiraId)
    {
        $this->carteiraId = $carteiraId;
    }

    public function handle(): void
    {
        $carteira = Carteira::find($this->carteiraId);
        if (!$carteira) {
            Log::error("Carteira não encontrada: ID {$this->carteiraId}");
            return;
        }
        
        $account = ConfigAccount::where('uuid', $carteira->id_config)->first();
        $v8 = new v8Service($account);

        $reponseV8 = $v8->GetConsulta($carteira);

        if ($reponseV8['error']) {
            $this->handleError($carteira, $reponseV8);
        } else {
            $this->handleSuccess($carteira, $reponseV8);
        }
    }

    private function handleError($carteira, $response)
    {
        $e = json_decode($response['message'], true);
        $error = $e['error']['message'] ?? $e['error'];

        $carteira->sit_sim = 2;
        $carteira->log_sim = $error;
        $carteira->save();

        DB::table($carteira->id_campanha)->where('cpf', $carteira->cpf)->update([
            'sit_sim' => 2,
            'log_sim' => $error,
            'updated_at' => now()
        ]);
        
        $this->AttCampanha($carteira);
    }

    private function handleSuccess($carteira, $response)
    {
        // $carteira->saldo_total = $response['emissionAmount'];
        $carteira->saldo_lib = $response['availableBalance'];
        $carteira->sit_sim = 1;
        $carteira->payload_sim = json_encode($response);
        $carteira->log_sim = "Simulação realizada com sucesso!";
        $carteira->save();

        DB::table($carteira->id_campanha)->where('cpf', $carteira->cpf)->update([
            // 'saldo_total' => $response['emissionAmount'],
            'saldo_lib' => $response['availableBalance'],
            'sit_sim' => 1,
            'payload_sim' => json_encode($response),
            'log_sim' => "Simulação realizada com sucesso!",
            'updated_at' => now()
        ]);
        
        $this->AttCampanha($carteira);
            
        JobProposta::dispatch($carteira->id)->onQueue('proposta');
        
    }
    
    private function AttCampanha($carteira)
    {
        // Recupera a campanha pelo UUID
        $campanha = Campanha::where('uuid', $carteira->id_campanha)->first();
        
        // Verifica se a campanha existe
        if ($campanha) {
            $successSaldo = DB::table($carteira->id_campanha)->where('sit_sim', 1)->count();
            $success_sim = DB::table($carteira->id_campanha)->where('sit_prop', 1)->count();
            $processados = DB::table($carteira->id_campanha)->where('sit_consulta', '<>',0)->count();
            
            $campanha->success_saldo = $successSaldo;
            $campanha->success_sim = $success_sim;
            $campanha->processados = $processados;
            
            $campanha->save();
        }
    }


}
