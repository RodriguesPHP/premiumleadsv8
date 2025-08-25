@include('layout.header')
<main role="main" class="main-content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-12">
        <div class="row align-items-center my-4">
          <div class="col">
            <h2 class="h3 mb-0 page-title">Campanhas</h2>
          </div>
          <div class="col-auto">
            <button type="button" class="btn mb-2 btn-primary" data-toggle="modal" data-target="#defaultModal"> <span class="fe fe-plus fe-12 mr-2"></span>Nova campanha</button>
          </div>
        </div>
        <div class="mb-2 align-items-center">
          <div class="card shadow mb-4">
            <div class="card-body">
              <table class="table table-bordered table-hover mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Registros</th>
                    <th>Cliente com Saldo</th>
                    <th>Total Consultados</th>
                    <th>Status</th>
                    <th>Criada em</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($campanhas as $campanha)
                  <tr>
                    <td>{{$campanha->uuid}}</td>
                    <td>{{$campanha->name}}</td>
                    <td>{{$campanha->registros}}</td>
                    <td>{{$campanha->success_saldo}}</td>
                    <td>{{$campanha->processados}}</td>
                    <td>
                      @if($campanha->sit == 1)
                      <span class="badge badge-pill badge-primary">Processando...</span>
                      @elseif($campanha->sit == 2)
                      <span class="badge badge-pill badge-success">Finalizada</span>
                      @else
                      <span class="badge badge-pill badge-info">Pausada</span>
                      @endif
                    </td>
                    <td>{{$campanha->created_at->format('d/m/Y H:i:s')}}</td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle" type="button" id="dr4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <span class="text-muted sr-only">Action</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dr4">
                          <a class="dropdown-item" href="{{route('campanhas.status',['campanha'=>$campanha->uuid,'tipo'=>'start'])}}">Iniciar</a>
                          <a class="dropdown-item" href="{{route('campanhas.status',['campanha'=>$campanha->uuid,'tipo'=>'pause'])}}">Pausar</a>
                          <a class="dropdown-item" href="{{route('campanhas.status',['campanha'=>$campanha->uuid,'tipo'=>'stop'])}}">Finalizar</a>
                          <a class="dropdown-item" href="{{route('campanhas.download',['campanha'=>$campanha->uuid])}}">Download</a>
                          <a class="dropdown-item" href="#">Excluir</a>
                        </div>
                      </div>
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
</main>

<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" style="display: none;" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="defaultModalLabel">Cadastrar Campanha</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{route('campanhas.store')}}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-group mb-3">
            <label for="simpleinput">Nome</label>
            <input type="text" id="name" name="name" class="form-control" required>
          </div>
          <div class="form-group mb-3">
            <label for="simpleinput">Arquivo</label>
            <input type="file" id="arquivo" name="arquivo" class="form-control-file" required>
            <a href="{{route('campanhas.downloadModelo')}}">Baixar modelo</a>
          </div>
          <div class="form-group mb-3">
            <label for="simpleinput">Banco</label>
            <select id="banco" name="banco" class="form-control">
              <option value="cartos">Cartos</option>
              <option value="qi-scd">QI</option>
            </select>
          </div>
          <div class="form-group mb-3">
            <label for="simpleinput">Contas V8 <a href="#" onclick="selectionall()">Selecionar Todos</a></label>
            @foreach($accounts as $account)
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="account[]" value="{{$account->uuid}}">
              <label class="form-check-label" for="account">{{$account->email}} | {{$account->bank}}</label>
            </div>
            @endforeach

          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn mb-2 btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn mb-2 btn-primary">Criar campanha</button>
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