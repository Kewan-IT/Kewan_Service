# Configuração de Backup Automático - KewanFarma

## Como funciona

O sistema agora possui:

1. **Backup Manual**: Campo nas configurações para fazer backup quando necessário
2. **Backup Automático**: Script que pode ser agendado para executar diariamente às 19h30

## Configurar o Backup Automático

### Passo 1: Tornar o script executável

```bash
chmod +x /caminho/do/projeto/backup-automatico.php
```

### Passo 2: Configurar o cron

Para agendar o backup automático para executar às 19h30 todos os dias:

```bash
crontab -e
```

Adicione a seguinte linha:

```cron
30 19 * * * /usr/bin/php /caminho/do/projeto/backup-automatico.php >> /caminho/do/projeto/storage/logs/backup.log 2>&1
```

**Explicação:**
- `30 19` = 19:30 (hora militar)
- `* * *` = todos os dias, todos os meses, todos os anos
- `/usr/bin/php` = caminho do PHP (verifique com `which php`)
- `/caminho/do/projeto/backup-automatico.php` = caminho completo do script
- `>> /caminho/do/projeto/storage/logs/backup.log 2>&1` = registrar log de execução

### Passo 3: Verificar a configuração

Para listar as tarefas agendadas:

```bash
crontab -l
```

## Logs

Os logs de execução do backup automático serão salvos em:
```
storage/logs/backup.log
```

## Localização dos Backups

Todos os backups são armazenados em:
```
storage/backups/backup_kewanfarma_YYYY-MM-DD_HH-MM-SS.sql
```

## Gerenciar Backups

Você pode gerenciar os backups através da página de configurações:

1. **Fazer Backup Manual**: Clique no botão "Fazer backup agora"
2. **Visualizar Backups**: A lista de todos os backups disponíveis é mostrada
3. **Descarregar**: Baixe um backup para sua máquina
4. **Deletar**: Remova backups antigos (o sistema também limpa automaticamente os com mais de 30 dias)

## Requisitos

- MySQL/MariaDB com o comando `mysqldump` instalado
- PHP CLI (command line interface)
- Acesso ao servidor para configurar o cron
- Permissão de escrita no diretório `storage/backups/`

## Exemplo de Crontab Completo

Se você tiver múltiplas tarefas agendadas:

```cron
# Backup automático às 19h30
30 19 * * * /usr/bin/php /var/www/kewanfarma/backup-automatico.php >> /var/www/kewanfarma/storage/logs/backup.log 2>&1

# Limpeza de arquivos temporários às 23h00
0 23 * * * /bin/rm -rf /var/www/kewanfarma/storage/tmp/*
```

## Troubleshooting

### O backup não está sendo executado

1. Verifique se o cron está ativo: `sudo service cron status`
2. Verifique o log: `tail -f storage/logs/backup.log`
3. Teste o comando manualmente: `/usr/bin/php backup-automatico.php`
4. Verifique as permissões: `ls -la backup-automatico.php`

### Erro "mysqldump não encontrado"

Instale as utilidades MySQL:
```bash
# Debian/Ubuntu
sudo apt-get install mysql-client

# CentOS/RHEL
sudo yum install mysql
```

### Disco cheio

Se o disco estiver cheio, os backups antigos serão automaticamente deletados. 
Você pode também deletar manualmente através da interface.

## Restaurar a partir de um Backup

Para restaurar um backup manualmente:

```bash
mysql -h localhost -u usuario -p banco_de_dados < storage/backups/backup_kewanfarma_YYYY-MM-DD_HH-MM-SS.sql
```
