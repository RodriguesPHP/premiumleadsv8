<?php

namespace App\Console\Commands;

use App\Jobs\DispararConsulta;
use App\Models\Campanha;
use App\Models\Carteira;
use App\Models\ConfigAccount;
use App\v8Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarCampanhas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campanhas:consulta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disparar consulta para o V8';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ids = Campanha::where('sit', 1)
            ->select(DB::raw('MIN(id) as id'))
            ->groupBy('user_id')
            ->pluck('id'); // só pega os IDs

        // 2. Buscar os registros completos usando os IDs
        $campanhas = Campanha::whereIn('id', $ids)->get();

        if ($campanhas->isEmpty()) {
            // Exibe mensagem se não houver campanhas
            $this->info('Nenhuma campanha em andamento!');
            return;
        }
        foreach ($campanhas as $campanha) {
            DispararConsulta::dispatch($campanha)->onQueue('consultacampanhas');
            $this->AttCampanha($campanha->uuid);
        }
    }


    private function AttCampanha($id_campanha)
    {
        // Recupera a campanha pelo UUID
        $campanha = Campanha::where('uuid', $id_campanha)->first();

        // Verifica se a campanha existe
        if ($campanha) {
            $successSaldo = DB::table($id_campanha)->where('sit_sim', 1)->count();
            $success_sim = DB::table($id_campanha)->where('sit_prop', 1)->count();
            $processados = DB::table($id_campanha)->where('sit_consulta', '<>', 0)->count();

            $campanha->success_saldo = $successSaldo;
            $campanha->success_sim = $success_sim;
            $campanha->processados = $processados;

            $campanha->save();
        }
    }
}
