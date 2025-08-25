<?php

namespace App\Http\Controllers;

use App\Models\ConfigAccount;
use App\Models\User;
use App\v8Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

class ConfigAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ConfigAccount::query();
        $users = User::where('role','user')->get();

        if (Auth::user()->role == "user") {
            $query->where('user_id', Auth::id());
            $users = [];
        }

        $configs = $query->with('user')->get();
        $grouped = $configs->groupBy('user_id');


        return view('config.index', compact('configs','users'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $account = ConfigAccount::where('email', $request->usuario)->first();
        if (!$account) {
            $account = new ConfigAccount;
            $account->user_id = $request->user ?? Auth::user()->id;
            $account->uuid = Uuid::uuid4()->toString();
            $account->email = $request->usuario;
            $account->senha = $request->senha;
            $account->audience = $request->audience;
            $account->client_id = $request->client_id;
            $account->link_id = $request->link_id;

            $v8 = new v8Service($account);
            $reponse = $v8->isAuth();
            if ($reponse) {
                $account->token = $reponse['access_token'];
                $account->save();
                $v8->webhook();
                return redirect()->back()->with(['success' => 'Usuario cadastrado com sucesso']);
            }

            return redirect()->back()->with(['error' => 'Credencias invalidas, tente novamente!']);
        } else {
            return redirect()->back()->with(['error' => 'Usuario já cadastrado !']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $account = ConfigAccount::where('uuid', $request->account)->first();
        if (!$account) {
            return redirect()->route('account.index')->with('error', 'Conta não localizada!');
        }
        $v8 = new v8Service($account);
        $tabelas = $v8->GetFees();
        $contract_links = $v8->GetContractLink();
        return view('config.account.edit', compact('account', 'tabelas', 'contract_links'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $account = ConfigAccount::where('uuid', $request->account)->first();
        if (!$account) {
            return redirect()->route('account.index')->with('error', 'Conta não localizada!');
        }
        $account->email = $request->email;
        $account->senha = $request->senha;
        $account->audience = $request->audience;
        $account->client_id = $request->client_id;
        $account->fees_id = $request->fees_id;
        $account->bank = $request->bank;
        $account->link_id = $request->link_id;
        $account->save();

        return redirect()->route('account.index')->with('success', 'Conta atualizada com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $configs = ConfigAccount::whereIn('uuid', $request->account)->get();
        foreach ($configs as $c) {
            $c->bank = $request->banco;
            $c->save();
        }

        return redirect()->route('account.index')->with('success', 'Contas atualizadas com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $account = ConfigAccount::where('uuid', $request->account)->first();
        if (!$account) {
            return redirect()->route('account.index')->with('error', 'Conta não localizada!');
        }

        $account->delete();
        return redirect()->route('account.index')->with('success', 'Conta deletada com sucesso!');
    }

    public function create_contractlink(Request $request)
    {
        $account = ConfigAccount::where('uuid', $request->account)->first();

        if (!$account) {
            return redirect()->route('account.index')->with('error', 'Conta não localizada!');
        }

        $providers = ['qi-scd', 'cartos'];

        foreach ($providers as $provider) {
            $v8 = new v8Service($account);
            $dados = ['provider' => $provider, 'feeId' => $request->feeId];
            $v8->createContractLink($dados);
        }

        return redirect()->back()->with('success', 'Links criados com sucesso!');
    }
}
