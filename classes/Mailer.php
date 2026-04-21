<?php
/**
 * includes/Mailer.php
 * Email helper. Uses PHP mail() by default.
 * For production, replace the send() method body with PHPMailer / SMTP.
 */
class Mailer
{
    // ── Public API ────────────────────────────────────────────────────────────

    public static function sendVerification(string $to, string $name, string $token): void
    {
        $link    = APP_URL . '/pages/verify_email.php?token=' . urlencode($token);
        $subject = 'Verify your ' . APP_NAME . ' account';
        $body    = self::verificationTemplate($name, $link);
        self::send($to, $name, $subject, $body);
    }

    public static function sendOTP(string $to, string $name, string $otp): void
    {
        $subject = 'Your ' . APP_NAME . ' password reset code';
        $body    = self::otpTemplate($name, $otp);
        self::send($to, $name, $subject, $body);
    }

    // ── Core sender ───────────────────────────────────────────────────────────

    private static function send(string $to, string $name, string $subject, string $htmlBody): bool
    {
        $fromName  = MAIL_FROM_NAME;
        $fromEmail = MAIL_FROM_ADDR;

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        // In development, log the email instead of sending it
        if (APP_ENV === 'development') {
            $logLine = "[MAIL " . date('Y-m-d H:i:s') . "] TO:{$to} SUBJECT:{$subject}\n";
            file_put_contents(BASE_PATH . '/mail.log', $logLine, FILE_APPEND);
            // Optionally still send in dev:
            // return @mail($to, $subject, $htmlBody, $headers);
            return true;
        }

        return @mail($to, $subject, $htmlBody, $headers);
    }

    // ── Email templates ───────────────────────────────────────────────────────

    private static function verificationTemplate(string $name, string $link): string
    {
        $appName = APP_NAME;
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#0f0f13;font-family:'Segoe UI',Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f13;padding:40px 20px;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#1a1a24;border-radius:16px;overflow:hidden;border:1px solid #2a2a3a;">
                <!-- Header -->
                <tr><td style="background:linear-gradient(135deg,#6c63ff,#3ecfcf);padding:40px;text-align:center;">
                  <h1 style="margin:0;color:#fff;font-size:28px;font-weight:700;letter-spacing:-0.5px;">{$appName}</h1>
                  <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:14px;">Account Verification</p>
                </td></tr>
                <!-- Body -->
                <tr><td style="padding:40px;">
                  <p style="color:#e0e0e0;font-size:16px;margin:0 0 16px;">Hi <strong style="color:#fff;">{$name}</strong>,</p>
                  <p style="color:#b0b0c0;font-size:15px;line-height:1.6;margin:0 0 32px;">
                    Thanks for joining {$appName}! Please verify your email address by clicking the button below.
                    This link expires in <strong style="color:#fff;">24 hours</strong>.
                  </p>
                  <div style="text-align:center;margin:0 0 32px;">
                    <a href="{$link}" style="display:inline-block;background:linear-gradient(135deg,#6c63ff,#3ecfcf);color:#fff;text-decoration:none;padding:16px 40px;border-radius:50px;font-size:16px;font-weight:600;letter-spacing:0.3px;">
                      Verify Email Address
                    </a>
                  </div>
                  <p style="color:#606070;font-size:13px;line-height:1.5;margin:0;">
                    If you didn't create this account, you can safely ignore this email.<br>
                    Or copy this link: <a href="{$link}" style="color:#6c63ff;">{$link}</a>
                  </p>
                </td></tr>
                <!-- Footer -->
                <tr><td style="background:#111118;padding:24px;text-align:center;border-top:1px solid #2a2a3a;">
                  <p style="margin:0;color:#404050;font-size:12px;">© {$appName} · All rights reserved</p>
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }

    private static function otpTemplate(string $name, string $otp): string
    {
        $appName = APP_NAME;
        $expiry  = OTP_EXPIRY_MINS;
        $digits  = str_split($otp);
        $otpHtml = '';
        foreach ($digits as $d) {
            $otpHtml .= "<span style='display:inline-block;width:44px;height:56px;line-height:56px;text-align:center;background:#0f0f18;border:2px solid #6c63ff;border-radius:10px;font-size:28px;font-weight:700;color:#fff;margin:0 4px;'>{$d}</span>";
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#0f0f13;font-family:'Segoe UI',Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f0f13;padding:40px 20px;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#1a1a24;border-radius:16px;overflow:hidden;border:1px solid #2a2a3a;">
                <tr><td style="background:linear-gradient(135deg,#6c63ff,#3ecfcf);padding:40px;text-align:center;">
                  <h1 style="margin:0;color:#fff;font-size:28px;font-weight:700;">{$appName}</h1>
                  <p style="margin:8px 0 0;color:rgba(255,255,255,0.8);font-size:14px;">Password Reset</p>
                </td></tr>
                <tr><td style="padding:40px;">
                  <p style="color:#e0e0e0;font-size:16px;margin:0 0 16px;">Hi <strong style="color:#fff;">{$name}</strong>,</p>
                  <p style="color:#b0b0c0;font-size:15px;line-height:1.6;margin:0 0 32px;">
                    Use the code below to reset your password. It expires in <strong style="color:#fff;">{$expiry} minutes</strong>.
                  </p>
                  <div style="text-align:center;margin:0 0 32px;">
                    {$otpHtml}
                  </div>
                  <p style="color:#606070;font-size:13px;line-height:1.5;margin:0;">
                    If you didn't request a password reset, please ignore this email.<br>
                    Your password will remain unchanged.
                  </p>
                </td></tr>
                <tr><td style="background:#111118;padding:24px;text-align:center;border-top:1px solid #2a2a3a;">
                  <p style="margin:0;color:#404050;font-size:12px;">© {$appName} · All rights reserved</p>
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }
}