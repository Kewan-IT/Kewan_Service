<?php
namespace App\Services;

class UploadService {
    private string $baseDir;
    private array  $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
    private array  $allowedDocs   = ['application/pdf'];
    private int    $maxSize;

    public function __construct() {
        $this->baseDir = dirname(__DIR__, 2) . '/storage/uploads/';
        $this->maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880);
    }

    public function uploadFoto(array $file, string $numeroFuncionario): ?string {
        return $this->upload($file, 'funcionarios/fotos/', $numeroFuncionario, $this->allowedImages);
    }

    public function uploadDocumento(array $file, string $numeroFuncionario, string $sufixo): ?string {
        return $this->upload($file, 'funcionarios/docs/', $numeroFuncionario . '_' . $sufixo, $this->allowedDocs);
    }

    private function upload(array $file, string $subdir, string $nome, array $tipos): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $this->maxSize) throw new \RuntimeException('Ficheiro demasiado grande.');

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $tipos, true)) throw new \RuntimeException('Tipo de ficheiro não permitido.');

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dest = $this->baseDir . $subdir . $nome . '.' . strtolower($ext);
        if (!move_uploaded_file($file['tmp_name'], $dest)) throw new \RuntimeException('Falha ao guardar o ficheiro.');

        return $subdir . $nome . '.' . strtolower($ext);
    }
}
