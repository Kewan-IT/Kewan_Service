<?php

namespace App\Models;

use Core\BaseModel;

class Funcionario extends BaseModel
{
    protected string $table = 'funcionarios';

    // ----------------------------------------------------------------
    // Lista funcionários com cargo — suporta pesquisa e filtro de status
    // ----------------------------------------------------------------
    public function listar(string $pesquisa = '', string $status = '', int $cargo_id = 0, int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($pesquisa !== '') {
            $where[]        = '(f.nome_completo LIKE :pesq1 OR f.numero_funcionario LIKE :pesq2 OR f.bi_numero LIKE :pesq3 OR f.telefone_principal LIKE :pesq4)';
            $like           = '%' . $pesquisa . '%';
            $params['pesq1'] = $like;
            $params['pesq2'] = $like;
            $params['pesq3'] = $like;
            $params['pesq4'] = $like;
        }
        if ($status !== '') {
            $where[]            = 'f.status = :status';
            $params['status']   = $status;
        }
        if ($cargo_id > 0) {
            $where[]            = 'f.cargo_id = :cargo_id';
            $params['cargo_id'] = $cargo_id;
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        // Total
        $stmtCount = $this->db->prepare("
            SELECT COUNT(*) FROM funcionarios f
            JOIN cargos c ON c.id = f.cargo_id
            WHERE $whereStr
        ");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        // Dados paginados
        $stmtData = $this->db->prepare("
            SELECT
                f.id, f.numero_funcionario, f.nome_completo,
                f.telefone_principal, f.email_pessoal,
                f.foto_url, f.status, f.data_admissao,
                f.tipo_contrato, f.salario,
                c.nome AS cargo,
                u.id   AS usuario_id,
                u.perfil, u.ativo AS acesso_activo
            FROM funcionarios f
            JOIN cargos c         ON c.id = f.cargo_id
            LEFT JOIN usuarios u  ON u.funcionario_id = f.id
            WHERE $whereStr
            ORDER BY f.nome_completo
            LIMIT $perPage OFFSET $offset
        ");
        $stmtData->execute($params);

        return [
            'data'         => $stmtData->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ];
    }

    // ----------------------------------------------------------------
    // Ficha completa de um funcionário
    // ----------------------------------------------------------------
    public function findComDetalhes(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                f.*,
                c.nome             AS cargo_nome,
                c.salario_base,
                u.id               AS usuario_id,
                u.email            AS usuario_email,
                u.perfil,
                u.ativo            AS acesso_activo,
                u.ultimo_login,
                u.tentativas_login,
                u.bloqueado_ate,
                adm.nome           AS criado_por_nome
            FROM funcionarios f
            JOIN cargos c          ON c.id = f.cargo_id
            LEFT JOIN usuarios u   ON u.funcionario_id = f.id
            LEFT JOIN usuarios adm ON adm.id = u.criado_por
            WHERE f.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Documentos anexos de um funcionário
    // ----------------------------------------------------------------
    public function documentos(int $funcionarioId): array
    {
        $stmt = $this->db->prepare("
            SELECT fd.*, u.nome AS carregado_por_nome
            FROM funcionarios_documentos fd
            LEFT JOIN usuarios u ON u.id = fd.carregado_por
            WHERE fd.funcionario_id = :id
            ORDER BY fd.criado_em DESC
        ");
        $stmt->execute(['id' => $funcionarioId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Histórico de credenciais
    // ----------------------------------------------------------------
    public function historicoCredenciais(int $funcionarioId): array
    {
        $stmt = $this->db->prepare("
            SELECT ch.*, u.nome AS executado_por_nome
            FROM credenciais_historico ch
            LEFT JOIN usuarios u ON u.id = ch.executado_por
            WHERE ch.funcionario_id = :id
            ORDER BY ch.criado_em DESC
            LIMIT 50
        ");
        $stmt->execute(['id' => $funcionarioId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Gera próximo número de funcionário  ex: KF-0042
    // ----------------------------------------------------------------
    public function proximoNumero(): string
    {
        $stmt = $this->db->query("SELECT MAX(id) FROM funcionarios");
        $max  = (int) $stmt->fetchColumn();
        return 'KF-' . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
    }

    // ----------------------------------------------------------------
    // Verifica unicidade de BI
    // ----------------------------------------------------------------
    public function biExiste(string $bi, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM funcionarios
            WHERE bi_numero = :bi AND id != :id
        ");
        $stmt->execute(['bi' => $bi, 'id' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------------
    // Estatísticas para o dashboard do módulo
    // ----------------------------------------------------------------
    public function estatisticas(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                                            AS total,
                SUM(status = 'activo')                             AS activos,
                SUM(status = 'inactivo' OR status = 'suspenso')    AS inactivos,
                SUM(status = 'desligado')                          AS desligados,
                SUM(CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END)  AS com_acesso
            FROM funcionarios f
            LEFT JOIN usuarios u ON u.funcionario_id = f.id
        ");
        return $stmt->fetch();
    }

    // ----------------------------------------------------------------
    // Adicionar documento extra
    // ----------------------------------------------------------------
    public function adicionarDocumento(array $dados): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO funcionarios_documentos
                (funcionario_id, tipo, titulo, ficheiro_url, ficheiro_nome, ficheiro_mime, ficheiro_tamanho, carregado_por)
            VALUES
                (:funcionario_id, :tipo, :titulo, :ficheiro_url, :ficheiro_nome, :ficheiro_mime, :ficheiro_tamanho, :carregado_por)
        ");
        $stmt->execute($dados);
        return (int) $this->db->lastInsertId();
    }

    // ----------------------------------------------------------------
    // Remover documento
    // ----------------------------------------------------------------
    public function removerDocumento(int $docId, int $funcionarioId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM funcionarios_documentos
            WHERE id = :id AND funcionario_id = :fid
        ");
        return $stmt->execute(['id' => $docId, 'fid' => $funcionarioId]);
    }
}
