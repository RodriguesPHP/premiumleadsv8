<?php

namespace App\Http\Controllers;

use App\Jobs\JobGetConsulta;
use App\Jobs\JobSimulacao;
use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\Models\Consulta;
use App\Models\Proposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\v8Service;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Webhook recebido', ['payload' => $payload]);

            $type = $payload['type'] ?? null;
            if (!$type) {
                return response()->json(['error' => 'Tipo não especificado no payload'], 200);
            }

            $cpf = $this->getCpf($payload['documentNumber'] ?? null);
            if (!$cpf) {
                Log::warning('CPF inválido ou ausente no payload', $payload);
                return response()->json(['error' => 'CPF inválido'], 200);
            }

            match ($type) {
                'balance.status.received.success' => $this->processSuccess($cpf, $payload),
                'balance.status.received.fail'    => $this->processFailure($cpf, $payload),
                'proposal.status.update'          => $this->updateProposal($payload),
                default                           => Log::info("Tipo desconhecido: {$type}", $payload),
            };

            return response()->json(['message' => 'Webhook processado com sucesso'], 200);
        } catch (\Throwable $e) {
            Log::error('Erro no processamento do webhook', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro interno no servidor'], 200);
        }
    }

    public function get(Request $request)
    {
        $account = ConfigAccount::where('uuid', $request->uuid)->first();
        if (!$account) {
            return response()->json(['message' => 'Conta não localizada']);
        }

        $v8 = new v8Service($account);
        $retorno = $v8->webhook();
        return redirect()->back()->with('success',$retorno['message']);
    }

    private function processSuccess($cpf, array $payload): void
    {
        $value = $payload['amount'] ?? $payload['balance'] ?? 0;

        $carteira = Carteira::where('cpf', $cpf)->latest()->first();
        if (!$carteira) {
            Log::warning('Nenhuma carteira encontrada para CPF', ['cpf' => $cpf]);
            return;
        }

        $carteira->update([
            'saldo_total' => $value,
            'sit'         => 1,
            'log'         => 'Autorizado',
        ]);

        DB::table($carteira->id_campanha)->where('cpf', $cpf)->update([
            'sit'         => 1,
            'saldo_total' => $value,
            'log'         => 'Autorizado',
            'updated_at'  => now(),
        ]);

        Log::info('Carteira atualizada com sucesso', ['cpf' => $cpf, 'valor' => $value]);

        if ($value > 0) {
            JobSimulacao::dispatch($carteira->id)->onQueue('Simulacao');
            Log::info('JobSimulacao disparado', ['cpf' => $cpf]);
        }
    }

    private function processFailure($cpf, array $payload): void
    {
        $carteira = Carteira::where('cpf', $cpf)->latest()->first();
        $mensagemErro = $payload['errorMessage'] ?? 'Erro não especificado';

        if ($carteira) {
            $carteira->update([
                'sit' => 2,
                'log' => $mensagemErro,
            ]);

            DB::table($carteira->id_campanha)->where('cpf', $cpf)->update([
                'sit' => 2,
                'log' => $mensagemErro,
            ]);

            Log::info('Carteira atualizada com falha', ['cpf' => $cpf, 'erro' => $mensagemErro]);
        } else {
            Log::warning('Falha recebida para CPF sem carteira associada', ['cpf' => $cpf]);
        }
    }

    private function updateProposal(array $payload): void
    {
        $proposal = Proposta::where('id_proposal', $payload['proposalId'])->first();

        if ($proposal) {
            $proposal->update([
                'status' => $payload['status'],
            ]);
        } else {
            Log::warning('Proposta não localizada', ['id_proposal' => $payload['proposalId']]);
        }
    }

    private function getCpf(?string $documentNumber): ?string
    {
        if (!$documentNumber) return null;

        $cpf = preg_replace('/\D/', '', $documentNumber);
        return strlen($cpf) === 11 ? $cpf : null;
    }

    private function formatar_cpf(string $cpf): string
    {
        $cpf = str_pad(preg_replace('/\D/', '', $cpf), 11, '0', STR_PAD_LEFT);
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
}
