<?php

namespace App\Jobs;

use App\Models\Campanha;
use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\v8Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispararConsulta implements ShouldQueue
{
    use Queueable;

    public  $campanha;

    /**
     * Create a new job instance.
     */ public function __construct(Campanha $campanha)
    {
        $this->campanha = $campanha;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campanha = $this->campanha;
        Log::info("Iniciando job para campanha: {$campanha->uuid}");

        $contas = json_decode($campanha->id_config) ?? [];
        if (empty($contas)) {
            Log::warning("Campanha {$campanha->uuid} não possui contas configuradas.");
            return;
        }

        foreach ($contas as $c) {
            Log::info("Processando conta: {$c} para campanha: {$campanha->uuid}");

            $cliente = DB::table($campanha->uuid)
                ->select(['cpf', 'nome', 'nasc', 'telefone', 'instancia'])
                ->where('sit_consulta', 0)
                ->first();

            if (!$cliente) {
                Log::info("Nenhum cliente encontrado para a campanha {$campanha->uuid}, marcando como encerrada.");
                $campanha->sit = 2;
                $campanha->save();
                continue;
            }

            Log::info("Cliente encontrado: {$cliente->cpf} - {$cliente->nome}");

            $account = ConfigAccount::where('uuid', $c)->first();
            if (!$account) {
                Log::warning("Conta {$c} não encontrada para campanha {$campanha->uuid}.");
                continue;
            }

            $v8 = new v8Service($account);

            $linkItem = collect(json_decode($account->link_id) ?? [])
                ->first(fn($item) => $item->provider === $campanha->provider);
            $link_id = $linkItem->id ?? null;
            Log::info("Link ID usado: {$link_id}");

            $reponseV8 = $v8->Consulta($cliente, $link_id);

            if ($reponseV8['error'] && !$reponseV8['try']) {
                Log::error("Erro na consulta V8 para cliente {$cliente->cpf}, tentativa falhou.");
                DB::table($campanha->uuid)
                    ->where('cpf', $cliente->cpf)
                    ->update(['sit_consulta' => 2, 'instancia' => $account->email]);
            } elseif (!$reponseV8['error']) {
                Log::info("Consulta V8 concluída com sucesso para cliente {$cliente->cpf}.");
                DB::table($campanha->uuid)
                    ->where('cpf', $cliente->cpf)
                    ->update(['sit_consulta' => 1, 'instancia' => $account->email]);

                $cliente->campanha = $campanha->uuid;
                $cliente->config = $c;
                $cliente->user_id = $campanha->user_id;

                $carteira = new Carteira;
                $carteira->CMinsert($cliente);

                Log::info("Cliente {$cliente->cpf} inserido na carteira.");
            }
        }

        Log::info("Job finalizado para campanha: {$campanha->uuid}");
    }
}
