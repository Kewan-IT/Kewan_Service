<?php
/**
 * Componente de Toast Flutuante
 * Exibe notificações flutuantes no canto superior direito
 */
?>

<style>
  .toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 9999;
    pointer-events: none;
  }

  .toast {
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    padding: 16px 20px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 320px;
    max-width: 400px;
    pointer-events: auto;
    animation: slideIn 0.3s ease-out;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(400px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes slideOut {
    from {
      opacity: 1;
      transform: translateX(0);
    }
    to {
      opacity: 0;
      transform: translateX(400px);
    }
  }

  .toast.hide {
    animation: slideOut 0.3s ease-out forwards;
  }

  .toast-icon {
    font-size: 20px;
    flex-shrink: 0;
    width: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .toast-content {
    flex: 1;
    font-size: 14px;
    line-height: 1.5;
  }

  .toast-content strong {
    display: block;
    margin-bottom: 2px;
    font-weight: 600;
  }

  .toast-close {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0;
    font-size: 18px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
  }

  .toast-close:hover {
    color: #333;
  }

  /* Variantes de cor */
  .toast.success .toast-icon {
    color: #28a745;
  }

  .toast.success {
    border-left: 4px solid #28a745;
  }

  .toast.error .toast-icon {
    color: #dc3545;
  }

  .toast.error {
    border-left: 4px solid #dc3545;
  }

  .toast.warning .toast-icon {
    color: #ffc107;
  }

  .toast.warning {
    border-left: 4px solid #ffc107;
  }

  .toast.info .toast-icon {
    color: #17a2b8;
  }

  .toast.info {
    border-left: 4px solid #17a2b8;
  }

  /* Responsive */
  @media (max-width: 640px) {
    .toast-container {
      top: 12px;
      right: 12px;
      left: 12px;
    }

    .toast {
      min-width: auto;
      max-width: none;
    }
  }
</style>

<div class="toast-container" id="toastContainer"></div>

<script>
  /**
   * Sistema de Toast Flutuante
   */
  class Toast {
    static container = document.getElementById('toastContainer');
    static autoHideDelay = 4000; // 4 segundos

    static show(message, type = 'info', title = null) {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;

      const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-exclamation-triangle-fill',
        warning: 'bi-exclamation-circle-fill',
        info: 'bi-info-circle-fill'
      };

      const icon = icons[type] || icons.info;

      toast.innerHTML = `
        <div class="toast-icon">
          <i class="bi ${icon}"></i>
        </div>
        <div class="toast-content">
          ${title ? `<strong>${title}</strong>` : ''}
          ${message}
        </div>
        <button type="button" class="toast-close" onclick="this.parentElement.remove()">
          <i class="bi bi-x-lg"></i>
        </button>
      `;

      this.container.appendChild(toast);

      // Auto remover após delay
      setTimeout(() => {
        if (toast.parentElement) {
          toast.classList.add('hide');
          setTimeout(() => toast.remove(), 300);
        }
      }, this.autoHideDelay);

      return toast;
    }

    static success(message, title = null) {
      return this.show(message, 'success', title || 'Sucesso!');
    }

    static error(message, title = null) {
      return this.show(message, 'error', title || 'Erro!');
    }

    static warning(message, title = null) {
      return this.show(message, 'warning', title || 'Atenção!');
    }

    static info(message, title = null) {
      return this.show(message, 'info', title || 'Informação');
    }
  }

  // Expor globalmente
  window.Toast = Toast;
</script>
