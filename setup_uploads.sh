#!/bin/bash
# Execute este script após deployment para configurar as permissões da pasta de uploads
# Uso: bash setup_uploads.sh

UPLOADS_DIR="$(dirname "$0")/public/uploads"

echo "Criando pastas de uploads..."
mkdir -p "$UPLOADS_DIR/funcionarios/fotos"
mkdir -p "$UPLOADS_DIR/funcionarios/docs"
mkdir -p "$UPLOADS_DIR/produtos"
mkdir -p "$UPLOADS_DIR/logos"

echo "Definindo permissões..."
chmod -R 775 "$UPLOADS_DIR"
chown -R www-data:www-data "$UPLOADS_DIR" 2>/dev/null || echo "(aviso: não foi possível mudar o dono — execute como root se necessário)"

echo "✓ Pastas de uploads configuradas em: $UPLOADS_DIR"
