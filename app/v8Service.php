<?php

namespace App;

use App\Models\ConfigAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class v8Service
{
    /**
     * Create a new class instance.
     */

    public ConfigAccount $account;

    public function __construct(ConfigAccount $account)
    {
        $this->account = $account;
    }

    public function Auth()
    {
        $response = Http::asForm()->post('https://auth.v8sistema.com/oauth/token', [
            'grant_type' => 'password',
            'username' => $this->account->email,
            'password' => $this->account->senha,
            'audience' => $this->account->audience,
            'scope' => 'offline_access',
            'client_id' => $this->account->client_id,
        ]);

        if ($response->successful()) {
            $this->account->token = $response->json()['access_token'];
            $this->account->save();
            return $response->json();
        } else {
            return false;
        }
    }

    public function isAuth()
    {
        $response = Http::asForm()->post('https://auth.v8sistema.com/oauth/token', [
            'grant_type' => 'password',
            'username' => $this->account->email,
            'password' => $this->account->senha,
            'audience' => $this->account->audience,
            'scope' => 'offline_access',
            'client_id' => $this->account->client_id,
        ]);

        if ($response->successful()) {
            $this->webhook();
            return $response->json();
        } else {
            return false;
        }
    }

    public function GetContractLink()
    {

        $this->Auth();
        $headers = [
            'Authorization' => 'Bearer ' . $this->account->token,
        ];
        $response = Http::withHeaders($headers)->get('https://bff.v8sistema.com/contract-link');

        // Verificando se a requisição foi bem-sucedida
        if ($response->successful()) {
            $data = $response->json();
            return $data;
        } else {
            // Se a requisição falhar, registre o erro
            Log::error('Erro na requisição GET', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }
    }

    public function Consulta($dados,$link_id)
    {
        try {
            $response = Http::retry(3, 5000)
                ->timeout(60)
                ->withHeaders([
                    'x-contract-link-id' => $link_id,
                ])
                ->post('https://bff.v8sistema.com/contract-link/fgts/balance', [
                    'documentNumber' => str_pad($dados->cpf, 11, "0", STR_PAD_LEFT),
                ])
                ->throw(); // pode lançar exceção

            return [
                'error' => false,
                'try'   => false,
                'data'  => $response->json(),
            ];
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return [
                'error' => true,
                'try'   => false,
                'message' => $e->getMessage(),
                'status'  => $e->response?->status(),
                'body'    => $e->response?->json(),
            ];
        }
    }

    public function FormatParcelas($parcelas)
    {
        return array_map(function ($item) {
            return [
                "totalAmount" => $item["amount"], // Renomeia o campo
                "dueDate" => $item["dueDate"]    // Mantém o campo 'dueDate'
            ];
        }, $parcelas);
    }
    public function GetConsulta($carteira)
    {
        try {
            $this->Auth(); // Autentica antes da chamada

            $queryParams = [
                'limit' => 1,
                'page' => 1,
                'search' => $carteira->cpf,
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $this->account->token,
            ];

            // Envia requisição GET para a API da V8
            $response = Http::withHeaders($headers)
                ->get('https://bff.v8sistema.com/fgts/balance', $queryParams);

            // Verifica se a requisição foi bem-sucedida
            if (!$response->successful()) {
                Log::error('Erro na requisição GET para consulta', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'error' => true,
                    'message' => 'Erro ao consultar saldo',
                    'status' => $response->status(),
                ];
            }

            $data = $response->json(); // Converte resposta em array
            Log::info('Resposta da consulta de saldo recebida', $data);

            // Verifica se existe dado válido e se status não é "pending"
            if (!empty($data['data']) &&
                is_array($data['data']) &&
                isset($data['data'][0]['status']) &&
                $data['data'][0]['status'] !== 'pending') {

                $dados = [
                    'simulationFeesId'     => $this->account->fees_id,
                    'balanceId'            => $data['data'][0]['id'],
                    'targetAmount'         => 0,
                    'documentNumber'       => $data['data'][0]['documentNumber'],
                    'isInsured'            => true,
                    'desiredInstallments'  => $this->FormatParcelas($data['data'][0]['periods']),
                    'provider'             => 'qi-scd', // Ou use lógica condicional se necessário
                ];

                return $this->Simulação($dados);
            } else {
                Log::info('Status pendente ou dados vazios para CPF', [
                    'cpf' => $carteira->cpf,
                    'response_data' => $data,
                ]);

                return [
                    'success' => false,
                    'error'=>true,
                    'message' => 'Saldo ainda pendente ou dados inválidos',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Erro interno em GetConsulta()', ['exception' => $e->getMessage()]);

            return [
                'error' => true,
                'message' => 'Erro interno no servidor',
                'exception' => $e->getMessage(),
            ];
        }
    }
    public function GetFees()
    {
        $this->Auth();
        $headers = [
            'Authorization' => 'Bearer ' . $this->account->token,
        ];
        $response = Http::withHeaders($headers)->get('https://bff.v8sistema.com/fgts/simulations/fees');

        // Verificando se a requisição foi bem-sucedida
        if ($response->successful()) {
            $data = $response->json();
            return $data;
        } else {
            // Se a requisição falhar, registre o erro
            Log::error('Erro na requisição GET', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }
    }

    public function CreateContractLink($dados)
    {
        $this->Auth();
        $headers = [
            'Authorization' => 'Bearer ' . $this->account->token,
        ];
        $response = Http::withHeaders($headers)->post('https://bff.v8sistema.com/contract-link',$dados);

        // Verificando se a requisição foi bem-sucedida
        if ($response->successful()) {
            $data = $response->json();
            return $data;
        } else {
            // Se a requisição falhar, registre o erro
            Log::error('Erro na requisição GET', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }
    }

    public function Simulação(array $dados)
    {
        $headers = [
            'authorization' => 'Bearer ' . $this->account->token,
            'content-type' => 'application/json',
        ];


        // Realizando a requisição POST
        $response = Http::withHeaders($headers)->post('https://bff.v8sistema.com/fgts/simulations', $dados);

        // Verificando resposta
        if ($response->successful()) {
            $dados = $response->json();
            $dados['error'] = false;
            return $dados;
        }

        // Em caso de erro
        Log::error('Erro na simulação', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $response = [
            'error' => true,
            'message' => $response->body()
        ];

        return $response;
    }

    public function proposta(array $dados)
    {
        $auth = $this->Auth();

        if (!$auth || !isset($auth['access_token'])) {
            return response()->json([
                'message' => 'Falha na autenticação.',
            ], 401);
        }

        $headers = [
            'authorization' => 'Bearer ' . $auth['access_token'],
            'content-type' => 'application/json',
        ];

        $payload = [
            "simulationFeesId" => $this->account->fees_id,
            "name" => $dados['nome'],
            "individualDocumentNumber" => str_pad($dados['cpf'], 11, "0", STR_PAD_LEFT),
            "documentIdentificationNumber" => "12345678",
            "motherName" => $dados['mae'] ?? "NAO INFORMADA",
            "nationality" => "Brasileiro(a)",
            "isPEP" => false,
            "email" => "email" . str_pad($dados['cpf'], 11, "0", STR_PAD_LEFT) . "@email.com",
            "birthDate" => $dados['nasc'],
            "personType" => "natural",
            "phone" => $dados['telefone'],
            "phoneCountryCode" => "55",
            "phoneRegionCode" => $dados['ddd'],
            "postalCode" => "30170002",
            "state" => "SC",
            "neighborhood" => "morretes",
            "addressNumber" => "115",
            "city" => "Itapema",
            "street" => "rua 118",
            "complement" => "casa",
            "formalizationLink" => "",
            "maritalStatus" => "single",
            "payment" => [
                "type" => "pix",
                "data" => [
                    "pix" => str_pad($dados['cpf'], 11, "0", STR_PAD_LEFT),
                ]
            ],
            "fgtsProposalsPeriods" => $dados['fgtsProposalsPeriods'],
            "fgtsSimulationId" => $dados['fgtsSimulationId']
        ];

        // Realizando a requisição POST
        $response = Http::withHeaders($headers)->post('https://bff.v8sistema.com/fgts/proposal', $payload);

        // Verificando resposta
        if ($response->successful()) {
            $dados = $response->json();
            $dados['error'] = false;
            return $dados;
        }

        // Em caso de erro
        Log::error('Erro ao enviar simulação FGTS', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $response = [
            'error' => true,
            'message' => $response->body()
        ];

        return $response;
    }

    public function webhook()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->account->token,
            'Content-Type' => 'application/json',
        ])
            ->post("https://bff.v8sistema.com/user/webhook/balance", [
                'url' => config('app.url') . '/api/webhook',
            ]);

        if ($response->successful()) {
            $response = [
                'error' => false,
                'message' => "Webhook configurado com sucesso",
                'url' => config('app.url') . '/api/webhook'
            ];
            return $response;
        } else {
            $response = [
                'error' => true,
                'message' => $response->body(),
                'url' => config('app.url') . '/api/webhook'
            ];

            return $response;
        }
    }
}
