<?php

namespace App\Models;

use Core\BaseModel;

class Cargo extends BaseModel
{
    protected string $table = 'cargos';

    public function listarActivos(): array
    {
        $stmt = $this->db->query("SELECT * FROM cargos WHERE ativo = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }
}
