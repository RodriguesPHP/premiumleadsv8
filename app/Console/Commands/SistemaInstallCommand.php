<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class SistemaInstallCommand extends Command
{
    protected $signature = 'sistema:install';
    protected $description = 'Instala o sistema: exibe config do banco, roda migrate, seed e cria config do supervisor';

    public function handle()
    {
        // Exibir configurações do banco de dados
        $this->info('⚙️ Configurações do banco de dados:');
        $this->line('Driver: ' . config('database.default'));
        $this->line('Host: ' . config('database.connections.mysql.host'));
        $this->line('Port: ' . config('database.connections.mysql.port'));
        $this->line('Database: ' . config('database.connections.mysql.database'));
        $this->line('Username: ' . config('database.connections.mysql.username'));

        // Confirmação do usuário
        if (!$this->confirm('Deseja continuar com a instalação?')) {
            $this->warn('Instalação cancelada.');
            return;
        }

        try {
            $this->call('migrate');
        } catch (\Throwable $e) {
            $this->error('Erro ao rodar migrate: ' . $e->getMessage());
        }
        
        // Rodar seed
        try {
            $this->call('db:seed');
        } catch (\Throwable $e) {
            $this->error('Erro ao rodar db:seed: ' . $e->getMessage());
        }

        // Criar arquivo .conf do Supervisor
        $url = config('app.url');
        $dbName = config('database.connections.mysql.database');
        $path = "/etc/supervisor/conf.d/premiumleadsv8_{$dbName}.conf";

        $projectPath = base_path();

        $content = <<<CONF
[program:premiumleadsv8_schedule_{$dbName}]
process_name=%(program_name)s
command=php artisan schedule:work
directory={$projectPath}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile={$projectPath}/storage/logs/{$dbName}_schedule.log

[program:premiumleadsv8_queue_{$dbName}]
process_name=%(program_name)s
command=php {$projectPath}/artisan queue:work --queue=Simulacao
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile={$projectPath}/storage/logs/{$dbName}_queue.log

[program:premiumleadsv8_consulta_{$dbName}]
process_name=%(program_name)s_%(process_num)02d
command=php {$projectPath}/artisan queue:work --queue=consultacampanhas
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile={$projectPath}/storage/logs/{$dbName}_consulta_%(process_num)02d.log
CONF;

        // Criar o arquivo
        File::put($path, $content);

        $this->info("✅ Arquivo Supervisor criado em: {$path}");
        $this->warn("⚠️ Não esqueça de rodar:");
        $this->line("supervisorctl reread");
        $this->line("supervisorctl update");
        
        $this->line("✅ Sistema instalado com sucesso");
        $this->line("URL: {$url}");
        $this->line("Usuario: admin@{$dbName}.com");
        $this->line("Senha: 102030@@");
        
        
    }
}
