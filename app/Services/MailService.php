<?php
namespace App\Services;

use App\Models\Configuracao;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * MailService — envia emails via SMTP configurado em Configurações.
 * Se o SMTP não estiver configurado, lança uma MailNaoConfiguradaException
 * para que o chamador possa oferecer um fallback (ex: senha temporária).
 */
class MailService
{
    private array $config;

    public function __construct()
    {
        $cfg = (new Configuracao())->getAllWithDefaults();
        $this->config = $cfg;
    }

    public function smtpConfigurado(): bool
    {
        return !empty($this->config['smtp_host'])
            && !empty($this->config['smtp_usuario'])
            && !empty($this->config['smtp_senha'])
            && !empty($this->config['smtp_de_email']);
    }

    /**
     * Envia email HTML.
     *
     * @throws \RuntimeException se o envio falhar
     * @throws \LogicException   se o SMTP não estiver configurado
     */
    public function enviar(string $para, string $paraNome, string $assunto, string $htmlBody): void
    {
        if (!$this->smtpConfigurado()) {
            throw new \LogicException('SMTP não configurado.');
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $this->config['smtp_host'];
        $mail->Port       = (int)($this->config['smtp_porta'] ?: 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->config['smtp_usuario'];
        $mail->Password   = $this->config['smtp_senha'];
        $mail->CharSet    = 'UTF-8';

        $cripto = strtolower($this->config['smtp_criptografia'] ?: 'tls');
        if ($cripto === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($cripto === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $deEmail = $this->config['smtp_de_email'];
        $deNome  = $this->config['smtp_de_nome'] ?: ($this->config['nome_farmacia'] ?: 'KewanFarma');

        $mail->setFrom($deEmail, $deNome);
        $mail->addAddress($para, $paraNome);
        $mail->addReplyTo($deEmail, $deNome);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        try {
            $mail->send();
        } catch (MailException $e) {
            throw new \RuntimeException('Falha ao enviar email: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /**
     * Email de recuperação de senha com link de reset.
     */
    public function enviarRecuperacaoSenha(string $para, string $nome, string $linkReset): void
    {
        $farmacia = htmlspecialchars($this->config['nome_farmacia'] ?: 'KewanFarma');
        $assunto  = "[$farmacia] Recuperação de senha";

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f4f7f6;font-family:'Segoe UI',Arial,sans-serif;">
  <div style="max-width:520px;margin:32px auto;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.08);overflow:hidden">
    <div style="background:#1a7f5a;padding:28px 32px;text-align:center">
      <div style="font-size:28px;color:#fff;font-weight:700;letter-spacing:-.3px">{$farmacia}</div>
      <div style="color:rgba(255,255,255,.75);font-size:13px;margin-top:4px">Sistema de Gestão</div>
    </div>
    <div style="padding:32px">
      <h2 style="margin:0 0 8px;font-size:20px;color:#1a1a1a">Recuperação de senha</h2>
      <p style="color:#555;line-height:1.6;margin:0 0 20px">
        Olá, <strong>{$nome}</strong>.<br>
        Recebemos um pedido para repor a sua senha de acesso ao sistema {$farmacia}.
      </p>
      <div style="text-align:center;margin:24px 0">
        <a href="{$linkReset}"
           style="background:#1a7f5a;color:#fff;padding:14px 32px;border-radius:8px;
                  text-decoration:none;font-weight:700;font-size:15px;display:inline-block">
          🔑 Redefinir minha senha
        </a>
      </div>
      <p style="color:#888;font-size:12px;line-height:1.6;margin:16px 0 0">
        Este link é válido por <strong>1 hora</strong>. Se não solicitou a recuperação de senha, ignore este email — a sua senha permanece inalterada.<br><br>
        Por razões de segurança, nunca partilhe este link com ninguém.
      </p>
    </div>
    <div style="background:#f8fffe;border-top:1px solid #e0f0e8;padding:16px 32px;text-align:center;font-size:11px;color:#aaa">
      {$farmacia} &middot; Este email foi gerado automaticamente, não responda.
    </div>
  </div>
</body>
</html>
HTML;

        $this->enviar($para, $nome, $assunto, $html);
    }
}
