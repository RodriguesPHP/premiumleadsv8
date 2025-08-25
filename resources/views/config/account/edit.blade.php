@include('layout.header')
<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="row align-items-center my-4">
                    <div class="col">
                        <h2 class="h3 mb-0 page-title">Contas V8 Digital - {{$account->email}}</h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal2"> <span class="fe fe-link fe-12 mr-2"></span>Ativar Links</button>
                    </div>
                </div>
                <div class="mb-2 align-items-center">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Usuario</label>
                                        <input type="text" id="email" name="email" class="form-control" value="{{$account->email}}">
                                    </div>

                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Senha</label>
                                        <input type="text" id="senha" name="senha" class="form-control" value="{{$account->senha}}">
                                    </div>

                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Audience</label>
                                        <input type="text" id="audience" name="audience" class="form-control" value="{{$account->audience}}">
                                    </div>

                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Client ID</label>
                                        <input type="text" id="client_id" name="client_id" class="form-control" value="{{$account->client_id}}">
                                    </div>

                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Tabela</label>
                                        <select name="fees_id" id="fees_id" class="form-control">
                                            @foreach($tabelas as $tabela)
                                                <option value="{{$tabela['simulation_fees']['id_simulation_fees']}}" {{ $account->fees_id == $tabela['simulation_fees']['id_simulation_fees'] ? 'selected' : '' }}>{{$tabela['simulation_fees']['label']}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Banco Consulta</label>
                                        <select name="bank" id="bank" class="form-control">
                                            <option value="qi-scd" {{ $account->bank == 'qi-scd' ? 'selected' : '' }}>QI</option>
                                            <option value="cartos" {{ $account->bank == 'cartos' ? 'selected' : '' }}>Cartos</option>
                                        </select>
                                    </div>
                                    <div class="col-4 form-group mb-3">
                                        <label for="simpleinput">Link ID</label>
                                        <select name="link_id" id="link_id" class="form-control">
                                            @foreach ($contract_links['data'] ?? [] as $c)
                                                <option value="{{json_encode($contract_links['data'])}}" {{ $account->link_id == $c['id'] ? 'selected' : '' }}>{{ $c['provider'] }} | {{ $c['feeName'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <button class="btn btn-primary m-2">Salvar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="defaultModalLabel">Nova Conta V8</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{route('account.store')}}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="simpleinput">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="simpleinput">Senha</label>
                    <input type="text" id="senha" name="senha" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="simpleinput">Audience</label>
                    <input type="text" id="audience" name="audience" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label for="simpleinput">Client ID</label>
                    <input type="text" id="client_id" name="client_id" class="form-control" required>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn mb-2 btn-primary">Criar conta</button>
        </div>
    </form>
        </div>
    </div>
</div>

<div class="modal fade" id="defaultModal2" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="defaultModalLabel">Ativar Links V8</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('account.create.contractlink',['account'=>$account->uuid])}}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="simpleinput">Tabela</label>
                        <select name="feeId" id="feeId" class="form-control">
                            @foreach($tabelas as $tabela)
                                <option value="{{$tabela['simulation_fees']['id_simulation_fees']}}" {{ $account->fees_id == $tabela['simulation_fees']['id_simulation_fees'] ? 'selected' : '' }}>{{$tabela['simulation_fees']['label']}}</option>
                            @endforeach
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn mb-2 btn-primary">Criar Links</button>
            </div>
            </form>
        </div>
    </div>
</div>
@include('layout.footer')
