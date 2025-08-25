@include('layout.header')
<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="row align-items-center my-4">
                    <div class="col">
                        <h2 class="h3 mb-0 page-title">Usuarios</h2>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal2"> <span class="fe fe-sync fe-12 mr-2"></span>Editar Conta</button>
                        <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal"> <span class="fe fe-user-plus fe-12 mr-2"></span>Nova Conta</button>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row p-2">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Criada em</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                        <tr>
                                            <td>{{$user->id}}</td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$user->email}}</td>
                                            <td>{{$user->created_at->format('d/m/Y H:i:s')}}</td>
                                            <td>
                                                <a class="btn btn-sm btn-danger" href="#">Excluir</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                <h5 class="modal-title" id="defaultModalLabel">Novo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('users.store')}}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="simpleinput">Nome</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="simpleinput">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="simpleinput">Senha</label>
                        <input type="text" id="password" name="password" class="form-control" required>
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