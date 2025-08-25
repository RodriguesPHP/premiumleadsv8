<?php

namespace App\Jobs;

use App\Models\Carteira;
use App\Models\CarteiraW;
use App\Models\ConfigAccount;
use App\Models\Proposta;
use App\v8Service;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobProposta implements ShouldQueue
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $carteira = Carteira::find($this->carteiraId);
        if (!$carteira) {
            Log::error("Carteira não encontrada: ID {$this->carteiraId}");
            return;
        }
        
        $account = ConfigAccount::where('uuid', $carteira->id_config)->first();
        $v8 = new v8Service($account);

        $payload = json_decode($carteira->payload_sim,true);

        $dados = [
            'nome'=>$carteira->nome,
            'cpf'=>$carteira->cpf,
            'saldo'=>$carteira->saldo_total,
            'fgtsSimulationId'=>$payload['id'],
            'nasc'=> DateTime::createFromFormat('d/m/Y', $carteira->nasc)->format('Y-m-d'),
            'telefone'=>substr($carteira->telefone, 2, 10),
            'ddd'=> substr($carteira->telefone, 0, 2),
            'fgtsProposalsPeriods'=>$payload['installments'],
        ];

        $responsev8 = $v8->proposta($dados);

        if ($responsev8['error']) {
            $this->handleError($carteira, $responsev8);
        } else {
            $this->handleSuccess($carteira, $responsev8);
        }
    }

    private function handleError($carteira, $response)
    {
        $e = json_decode($response['message'], true);
        $error = $e['error']['message'] ?? $e['error'];

        $carteira->sit_prop = 2;
        $carteira->log_prop = $error;
        $carteira->save();

        DB::table($carteira->id_campanha)->where('cpf', $carteira->cpf)->update([
            'sit_prop' => 2,
            'log_prop' => $error,
            'updated_at' => now()
        ]);
    }

    private function handleSuccess($carteira, $response)
    {
        $proposta = new Proposta;
        $proposta->id_cliente = $carteira->uuid;
        $proposta->id_proposal = $response['id'];
        $proposta->number_proposal = $response['contractNumber'];
        $proposta->value_proposal = $carteira->saldo_lib;
        $proposta->link_proposal = $response['formalizationLink'];
        $proposta->status = "pending";
        $proposta->id_config = $carteira->id_config;
        $proposta->save();

        $carteira->sit_prop = 1;
        $carteira->id_proposal = $response['id'];
        $carteira->log_prop = "Proposta gerada com sucesso!";
        $carteira->save();

        DB::table($carteira->id_campanha)->where('cpf',$carteira->cpf)->update([
            'sit_prop' => $carteira->sit_prop,
            'id_proposal'=>$carteira->id_proposal,
            'log_prop' => $carteira->log_prop,
            'updated_at'=>now()
        ]);
    }
}
