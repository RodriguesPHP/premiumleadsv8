@include('layout.header')
<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="row align-items-center my-4">
                    <div class="col">
                        <h2 class="h3 mb-0 page-title">Contas V8 Digital</h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal2"> <span class="fe fe-sync fe-12 mr-2"></span>Alterar Banco</button>
                        <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal"> <span class="fe fe-user-plus fe-12 mr-2"></span>Nova Conta</button>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row p-2">
                                @foreach($configs as $config)
                                <div class="col col-md-3">
                                    <div class="card shadow mb-4">
                                        <div class="card-body text-center">
                                            <div class="avatar avatar-lg mt-4">
                                                <a href="">
                                                    <img src="https://api.scalar.com/cdn/images/_ehD7LcNkzqo4MdzFwyOz/ba8749xyt0TzaEzQTNkjL.png" alt="..." class="avatar-img rounded-circle">
                                                </a>
                                            </div>
                                            <div class="card-text my-2">
                                                <strong class="card-title my-0">{{$config->email}} </strong>
                                                <p class="small text-muted mb-0">{{$config->user->name}}</p>
                                                <p class="small"><span class="badge badge-light text-muted">V8 Digital</span></p>
                                            </div>
                                        </div> <!-- ./card-text -->
                                        <div class="card-footer">
                                            <div class="row align-items-center justify-content-between">
                                                <div class="col-auto">
                                                   <a class="btn btn-sm btn-primary" href="{{route('account.edit',['account'=>$config->uuid])}}"><i class="fe fe-edit fe-12"></i></a>
                                                            <a class="btn btn-sm btn-primary" href="{{route('webhook.get',['uuid'=>$config->uuid])}}"><i class="fe fe-wifi fe-12"></i></a>
                                                </div>
                                                <div class="col-auto">
                                                        <a class="btn btn-sm btn-danger" href="{{route('account.delete',['account'=>$config->uuid])}}"><i class="fe fe-trash fe-12"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- /.card-footer -->
                                    </div>
                                </div>
                                @endforeach
                            </div>
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
                        <input type="text" id="audience" name="audience" class="form-control" value="https://bff.v8sistema.com" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="simpleinput">Client ID</label>
                        <input type="text" id="client_id" name="client_id" class="form-control" value="DHWogdaYmEI8n5bwwxPDzulMlSK7dwIn" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="simpleinput">Link ID</label>
                        <input type="text" id="link_id" name="link_id" class="form-control">
                    </div>
                    @if (Auth::user()->role == "admin")
                    <div class="form-group mb-3">
                        <label for="simpleinput">Usuario</label>
                        <select name="user" id="user" class="form-control">
                            @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @endif

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
                <h5 class="modal-title" id="defaultModalLabel">Alterar Banco</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('account.update')}}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="simpleinput">Bancos</label>
                        <select id="banco" name="banco" class="form-control">
                            <option value="cartos">Cartos</option>
                            <option value="qi-scd">QI</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="simpleinput">Usuarios<a href="#" onclick="selectionall()">Selecionar Todos</a></label>
                        @foreach($configs as $account)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="account[]" value="{{$account->uuid}}">
                            <label class="form-check-label" for="account">{{$account->email}} | {{$account->bank}}</label>
                        </div>
                        @endforeach
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn mb-2 btn-primary">Alterar</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    function selectionall() {
        const elems = document.getElementsByName('account[]');
        elems.forEach(elem => {
            elem.checked = !elem.checked;
        });
    }
</script>
@include('layout.footer')