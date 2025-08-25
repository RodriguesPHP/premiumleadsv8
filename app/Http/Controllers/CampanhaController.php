<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\ConfigAccount;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Response;

class CampanhaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campanhasQuery = Campanha::query();
        $accountsQuery  = ConfigAccount::query();

        if (Auth::user()->role === "user") {
            $campanhasQuery->where('user_id', Auth::id());
            $accountsQuery->where('user_id', Auth::id());
        }

        $campanhas = $campanhasQuery->orderBy('created_at', 'desc')->get();
        $accounts  = $accountsQuery->get();

        return view('campanhas.index', compact('campanhas', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {

            $filePath = $request->file('arquivo')->store('uploads');

            $fullPath = storage_path("app/private/$filePath");
            $fullPath = str_replace('\\', '/', $fullPath);

            $campanha = new Campanha;
            $campanha->user_id = Auth::user()->id;
            $campanha->uuid = Uuid::uuid4()->toString();
            $campanha->name = $request->name;
            $campanha->provider = $request->banco;
            $campanha->id_config = json_encode($request->account);
            $campanha->id_wa = $request->wa ?? 0;
            $campanha->save();

            if ($this->create_table($campanha, $fullPath)) {
                $campanha->registros = DB::table($campanha->uuid)->count();
                $campanha->save();
                return redirect()->back()->with('success', 'Campanha criada com sucesso!');
            } else {
                $campanha->delete();
                return redirect()->back()->with('error', 'Não foi possivel criar a campanha');
            }
        }

        return redirect()->back()->with('error', 'Arquivo inválido ou não enviado.');
    }

    private function create_table($campanha, $filePath)
    {
        $table = $campanha->uuid;
        Schema::create($table, function (Blueprint $table) {
            $table->string('cpf');
            $table->string('nome');
            $table->string('nasc');
            $table->string('telefone');
            $table->string('instancia')->nullable();
            $table->string('saldo_total')->nullable(); 
            $table->string('saldo_lib')->nullable();
            $table->text('payload_sim')->nullable();
            $table->integer('sit_consulta')->default(0)->nullable();
            $table->integer('sit')->default(0)->nullable();
            $table->integer('sit_sim')->default(0)->nullable();
            $table->integer('sit_prop')->default(0)->nullable();
            $table->text('log')->nullable();
            $table->text('log_sim')->nullable();
            $table->text('log_prop')->nullable();
            $table->string('id_proposal')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable($table)) {
            DB::statement("
                LOAD DATA INFILE '$filePath'
                INTO TABLE `$table`
                FIELDS TERMINATED BY ';' 
                LINES TERMINATED BY '\r\n'
                IGNORE 1 ROWS
                (cpf,nome,nasc,telefone)
                SET created_at = NOW(),
                updated_at = NOW()
            ");

            return true;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Campanha $campanha)
    {
        //
    }

    public function status(Request $request)
    {
        $campanha = Campanha::where('uuid', $request->campanha)->first();
        if (!$campanha) {
            return redirect()->route('campanhas.index')->with('error', 'Campanha não localizada!');
        }
        switch ($request->tipo) {
            case 'stop':
                $campanha->sit = 2;
                $campanha->save();
                break;

            case 'start':
                $campanha->sit = 1;
                $campanha->save();
                break;
            default:
                $campanha->sit = 0;
                $campanha->save();
                break;
        }
        return redirect()->route('campanhas.index')->with('success', 'Campanha alterada com sucesso!');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campanha $campanha)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campanha $campanha)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campanha $campanha)
    {
        //
    }

    public function download(Request $request)
    {
        // Localiza a campanha pelo UUID
        $campanha = Campanha::where('uuid', $request->campanha)->first();

        if (!$campanha) {
            return redirect()->route('campanhas.index')->with('error', 'Campanha não localizada!');
        }

        // Busca os dados da tabela dinâmica
        $dados = DB::table("{$campanha->uuid} AS c")->where('saldo_total', '>', 60)->get();
        if ($dados->isEmpty()) {
            return redirect()->route('campanhas.index')->with('error', 'Nenhum dado encontrado para download!');
        }

        // Cabeçalho a partir do primeiro item
        $headers = array_keys((array) $dados->first());

        // Gera o CSV na memória
        $callback = function () use ($dados, $headers) {
            $handle = fopen('php://output', 'w');

            // Evita problemas com encoding (Excel etc.)
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Escreve o cabeçalho
            fputcsv($handle, $headers, ';');

            // Escreve os dados
            foreach ($dados as $linha) {
                fputcsv($handle, (array) $linha, ';');
            }

            fclose($handle);
        };

        $filename = "{$campanha->name}.csv";

        // Retorna a resposta como download
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }



    public function downloadModelo(Request $request)
    {
        // Define os dados fixos
        $dados = collect([
            [
                'cpf' => '12345678312',
                'nome' => 'TESTE',
                'nasc' => '01/01/2024',
                'telefone' => '319932321'
            ]
        ]);

        // Cabeçalho a partir da primeira linha
        $headers = array_keys($dados->first());

        // Gera o CSV na memória
        $callback = function () use ($dados, $headers) {
            $handle = fopen('php://output', 'w');

            // Evita problemas com Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Escreve cabeçalho
            fputcsv($handle, $headers, ';');

            // Escreve os dados
            foreach ($dados as $linha) {
                fputcsv($handle, $linha, ';');
            }

            fclose($handle);
        };

        $filename = "base_modelo.csv";

        // Retorna a resposta como download
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
