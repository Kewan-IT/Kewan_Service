<?php

namespace App\Models;

use Core\BaseModel;

class Configuracao extends BaseModel
{
    protected string $table = 'configuracoes';

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT chave, valor FROM configuracoes ORDER BY chave ASC");
        $rows = $stmt->fetchAll();

        $config = [];
        foreach ($rows as $row) {
            $config[$row['chave']] = $row['valor'];
        }

        return $config;
    }

    public function getAllWithDefaults(): array
    {
        $config = $this->getAll();

        $defaults = [
            'nome_farmacia' => 'KewanFarma',
            'nuit_farmacia' => '',
            'endereco_farmacia' => '',
            'telefone_farmacia' => '',
            'email_farmacia' => '',
            'logo_farmacia' => '',
            'moeda' => 'MZN',
            'iva_percentagem' => '16',
            'prefixo_venda' => 'VD',
            'prefixo_compra' => 'CP',
            'dias_alerta_validade' => '90',
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $config) || $config[$key] === null) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    public function salvar(array $dados): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO configuracoes (chave, valor, descricao)
             VALUES (:chave, :valor, :descricao)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor), descricao = VALUES(descricao)"
        );

        foreach ($dados as $chave => $valor) {
            $stmt->execute([
                'chave' => $chave,
                'valor' => $valor,
                'descricao' => $this->descricaoParaChave($chave),
            ]);
        }
    }

    private function descricaoParaChave(string $chave): string
    {
        return match ($chave) {
            'nome_farmacia' => 'Nome da farmácia',
            'logo_farmacia' => 'Logo da farmácia',
            'nuit_farmacia' => 'NUIT da farmácia',
            'endereco_farmacia' => 'Endereço da farmácia',
            'telefone_farmacia' => 'Telefone da farmácia',
            'email_farmacia' => 'Email de contacto da farmácia',
            'moeda' => 'Moeda utilizada no sistema',
            'iva_percentagem' => 'Percentagem de IVA aplicada',
            'prefixo_venda' => 'Prefixo das vendas',
            'prefixo_compra' => 'Prefixo das compras',
            'dias_alerta_validade'     => 'Dias de antecedência para alertas de validade',
            'backup_hora_automatico'  => 'Hora diária para execução do backup automático',
            default => 'Configuração do sistema',
        };
    }
}
