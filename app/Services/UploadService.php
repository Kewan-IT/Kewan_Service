<?php
namespace App\Services;

class UploadService
{
    private string $baseDir;
    private array  $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
    private array  $allowedDocs   = ['application/pdf'];
    private int    $maxSize;

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__, 2) . '/public/uploads/';
        $this->maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880); // 5 MB
    }

    public function uploadFoto(array $file, string $numeroFuncionario): ?string
    {
        return $this->upload($file, 'funcionarios/fotos/', $numeroFuncionario, $this->allowedImages);
    }

    public function uploadLogo(array $file): ?string
    {
        return $this->upload($file, 'logos/', 'logo_' . time(), $this->allowedImages);
    }

    public function uploadDocumento(array $file, string $numeroFuncionario, string $sufixo): ?string
    {
        return $this->upload($file, 'funcionarios/docs/', $numeroFuncionario . '_' . $sufixo, $this->allowedDocs);
    }

    public function uploadProduto(array $file, string $nomeProduto): ?string
    {
        return $this->upload($file, 'produtos/', $this->slugify($nomeProduto) . '-' . uniqid(), $this->allowedImages);
    }

    // Retorna o caminho absoluto no sistema de ficheiros dado um caminho relativo
    public function caminhoAbsoluto(string $relativePath): string
    {
        return $this->baseDir . $relativePath;
    }

    // Apaga um ficheiro de upload anterior (antes de substituir)
    public function apagar(string $relativePath): void
    {
        $abs = $this->caminhoAbsoluto($relativePath);
        if ($relativePath && is_file($abs)) {
            @unlink($abs);
        }
    }

    private function upload(array $file, string $subdir, string $nome, array $tipos): ?string
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erro no upload (código ' . $file['error'] . ').');
        }
        if ($file['size'] > $this->maxSize) {
            throw new \RuntimeException('Ficheiro demasiado grande (máx. ' . ($this->maxSize / 1048576) . ' MB).');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $tipos, true)) {
            throw new \RuntimeException('Tipo de ficheiro não permitido. Permitidos: ' . implode(', ', $tipos));
        }

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $destDir = $this->baseDir . $subdir;

        // Criar pasta automaticamente se não existir
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0775, true)) {
                throw new \RuntimeException('Não foi possível criar a pasta de destino: ' . $destDir);
            }
        }

        $dest = $destDir . $nome . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Falha ao guardar o ficheiro. Verifique as permissões da pasta: ' . $destDir);
        }

        return $subdir . $nome . '.' . $ext;
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[áàâãä]/u', 'a', $text);
        $text = preg_replace('/[éèêë]/u', 'e', $text);
        $text = preg_replace('/[íìîï]/u', 'i', $text);
        $text = preg_replace('/[óòôõö]/u', 'o', $text);
        $text = preg_replace('/[úùûü]/u', 'u', $text);
        $text = preg_replace('/[ç]/u', 'c', $text);
        return preg_replace('/[^a-z0-9]+/', '-', $text);
    }
}
