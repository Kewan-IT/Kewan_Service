<?php

namespace App\Models;

use Core\BaseModel;

class Usuario extends BaseModel
{
    protected string $table = 'usuarios';

    // ----------------------------------------------------------------
    // Busca utilizador por email (para login)
    // ----------------------------------------------------------------
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.*,
                f.foto_url,
                f.nome_completo AS funcionario_nome,
                c.nome          AS cargo
            FROM usuarios u
            LEFT JOIN funcionarios f ON f.id = u.funcionario_id
            LEFT JOIN cargos c       ON c.id = f.cargo_id
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Lista utilizadores com dados do funcionário associado
    // ----------------------------------------------------------------
    public function listarComFuncionario(): array
    {
        $stmt = $this->db->query("
            SELECT
                u.id,
                u.nome,
                u.email,
                u.perfil,
                u.ativo,
                u.ultimo_login,
                u.tentativas_login,
                u.bloqueado_ate,
                u.criado_em,
                f.nome_completo  AS funcionario_nome,
                f.numero_funcionario,
                f.foto_url,
                c.nome           AS cargo,
                adm.nome         AS criado_por_nome
            FROM usuarios u
            LEFT JOIN funcionarios f  ON f.id  = u.funcionario_id
            LEFT JOIN cargos c        ON c.id  = f.cargo_id
            LEFT JOIN usuarios adm    ON adm.id = u.criado_por
            ORDER BY u.criado_em DESC
        ");
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Cria credenciais para um funcionário (usado pelo admin)
    // ----------------------------------------------------------------
    public function criarCredenciais(array $dados, int $adminId): int
    {
        $dados['senha_hash'] = password_hash($dados['senha'], PASSWORD_BCRYPT, ['cost' => 12]);
        unset($dados['senha']);
        $dados['criado_por'] = $adminId;

        $id = $this->insert($dados);

        // Registar no histórico de credenciais
        $stmt = $this->db->prepare("
            INSERT INTO credenciais_historico
                (usuario_id, funcionario_id, acao, perfil_novo, executado_por)
            VALUES
                (:usuario_id, :funcionario_id, 'criacao', :perfil_novo, :executado_por)
        ");
        $stmt->execute([
            'usuario_id'     => $id,
            'funcionario_id' => $dados['funcionario_id'],
            'perfil_novo'    => $dados['perfil'],
            'executado_por'  => $adminId,
        ]);

        return $id;
    }

    // ----------------------------------------------------------------
    // Altera a senha do utilizador
    // ----------------------------------------------------------------
    public function alterarSenha(int $id, string $novaSenha): bool
    {
        return $this->update($id, [
            'senha_hash'      => password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]),
            'token_reset'     => null,
            'token_expira_em' => null,
            'tentativas_login'=> 0,
            'bloqueado_ate'   => null,
        ]);
    }

    // ----------------------------------------------------------------
    // Desbloqueia uma conta manualmente (admin)
    // ----------------------------------------------------------------
    public function desbloquear(int $id): bool
    {
        return $this->update($id, [
            'tentativas_login' => 0,
            'bloqueado_ate'    => null,
        ]);
    }

    // ----------------------------------------------------------------
    // Verifica se já existe utilizador com o email dado
    // ----------------------------------------------------------------
    public function emailExiste(string $email, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuarios
            WHERE email = :email AND id != :id
        ");
        $stmt->execute(['email' => $email, 'id' => $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------------
    // Utilizadores sem conta associada a funcionários
    // ----------------------------------------------------------------
    public function funcionariosSemConta(): array
    {
        $stmt = $this->db->query("
            SELECT f.id, f.nome_completo, f.numero_funcionario, c.nome AS cargo
            FROM funcionarios f
            LEFT JOIN cargos c     ON c.id = f.cargo_id
            LEFT JOIN usuarios u   ON u.funcionario_id = f.id
            WHERE u.id IS NULL AND f.status = 'activo'
            ORDER BY f.nome_completo
        ");
        return $stmt->fetchAll();
    }
}
