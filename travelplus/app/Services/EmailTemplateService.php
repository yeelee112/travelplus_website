<?php

namespace App\Services;

class EmailTemplateService
{
    /**
     * @param array<string, string> $details
     * @param array<int, array{label:string,value:string}> $rows
     */
    public function render(
        string $eyebrow,
        string $title,
        string $intro,
        array $details,
        array $rows,
        string $message = '',
        string $ctaLabel = '',
        string $ctaUrl = ''
    ): string {
        $websiteSettings = new WebsiteSettingsService();
        $contactPhoneDisplay = $websiteSettings->get('hotline_en');
        $contactEmail = $websiteSettings->get('email');
        $logoUrl = $this->assetUrl('assets/images/logo.svg');
        $companyProfileUrl = $this->assetUrl('assets/images/TravelPlus_CompanyProfile.png');
        $safeCtaUrl = $this->e($ctaUrl);
        $ctaHtml = '';

        if ($ctaLabel !== '' && $ctaUrl !== '') {
            $ctaHtml = '
                <tr>
                    <td style="padding:8px 0 0;">
                        <a href="' . $safeCtaUrl . '" class="email-cta" style="display:inline-block;background:#0ea5e9;color:#ffffff;text-decoration:none;font-weight:800;font-size:15px;line-height:20px;padding:14px 22px;border-radius:999px;">
                            ' . $this->e($ctaLabel) . '
                        </a>
                    </td>
                </tr>';
        }

        return '<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>' . $this->e($title) . '</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-page { padding: 12px 6px !important; }
            .email-shell { border-radius: 12px !important; }
            .email-header { padding: 20px 16px 18px !important; }
            .email-brand-logo img { width: 138px !important; max-width: 138px !important; padding: 7px !important; border-radius: 9px !important; }
            .email-brand-name { display: none !important; width: 0 !important; max-width: 0 !important; overflow: hidden !important; font-size: 0 !important; }
            .email-heading { padding-top: 18px !important; }
            .email-title { font-size: 24px !important; line-height: 1.22 !important; }
            .email-intro { margin-top: 10px !important; font-size: 15px !important; line-height: 1.55 !important; }
            .email-details-section { padding: 18px 16px 4px !important; }
            .email-rows-section { padding: 8px 16px 18px !important; }
            .email-detail-cell { display: block !important; width: 100% !important; padding: 0 0 10px !important; box-sizing: border-box !important; }
            .email-detail-spacer { display: none !important; width: 0 !important; height: 0 !important; overflow: hidden !important; }
            .email-detail-card { padding: 12px 14px !important; border-radius: 12px !important; }
            .email-row-label, .email-row-value { display: block !important; width: 100% !important; box-sizing: border-box !important; }
            .email-row-label { padding: 12px 14px 5px !important; border-bottom: 0 !important; }
            .email-row-value { padding: 0 14px 12px !important; font-size: 14px !important; line-height: 1.5 !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
            .email-note-section { padding: 0 16px 18px !important; }
            .email-note { padding: 14px !important; border-radius: 12px !important; }
            .email-promo-section { padding: 0 16px 20px !important; }
            .email-promo { border-radius: 14px !important; }
            .email-promo-copy { padding: 18px !important; }
            .email-promo-title { font-size: 18px !important; }
            .email-promo-image { display: none !important; width: 0 !important; max-width: 0 !important; overflow: hidden !important; }
            .email-cta-table { width: 100% !important; }
            .email-cta { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
            .email-footer { padding: 16px !important; font-size: 12px !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#eef8fb;color:#111827;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . $this->e($intro) . '</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-page" style="background:#eef8fb;margin:0;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-shell" style="width:100%;max-width:680px;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #cfeef9;box-shadow:0 20px 50px rgba(14,165,233,.12);">
                    <tr>
                        <td class="email-header" style="background:linear-gradient(135deg,#ecfbff 0%,#ffffff 42%,#f3fae9 100%);padding:28px 28px 22px;border-bottom:1px solid #d7f0f8;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="email-brand-logo" style="vertical-align:middle;">
                                        <img src="' . $this->e($logoUrl) . '" width="178" alt="Travel Plus" style="display:block;max-width:178px;height:auto;border:0;background:#ffffff;border-radius:12px;padding:10px;">
                                    </td>
                                    <td align="right" class="email-brand-name" style="vertical-align:middle;color:#0ea5e9;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">
                                        Travel Plus Vietnam
                                    </td>
                                </tr>
                            </table>
                            <div class="email-heading" style="padding-top:26px;">
                                <div style="color:#0ea5e9;font-size:12px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px;">' . $this->e($eyebrow) . '</div>
                                <h1 class="email-title" style="margin:0;color:#07111f;font-size:30px;line-height:1.16;font-weight:900;letter-spacing:0;">' . $this->e($title) . '</h1>
                                <p class="email-intro" style="margin:12px 0 0;color:#465466;font-size:16px;line-height:1.65;">' . $this->e($intro) . '</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-details-section" style="padding:26px 28px 6px;">
                            ' . $this->renderDetails($details) . '
                        </td>
                    </tr>
                    <tr>
                        <td class="email-rows-section" style="padding:12px 28px 22px;">
                            ' . $this->renderRows($rows) . '
                        </td>
                    </tr>
                    ' . ($message !== '' ? '
                    <tr>
                        <td class="email-note-section" style="padding:0 28px 24px;">
                            <div class="email-note" style="background:#f7fbfd;border:1px solid #d9edf5;border-radius:18px;padding:18px 20px;">
                                <div style="color:#64748b;font-size:12px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;">Nội dung ghi chú</div>
                                <div style="color:#1f2937;font-size:15px;line-height:1.7;">' . nl2br($this->e($message)) . '</div>
                            </div>
                        </td>
                    </tr>' : '') . '
                    <tr>
                        <td class="email-promo-section" style="padding:0 28px 30px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="email-promo" style="background:#07111f;border-radius:20px;overflow:hidden;">
                                <tr>
                                    <td class="email-promo-copy" style="padding:22px;color:#ffffff;">
                                        <div style="color:#87d36d;font-size:12px;font-weight:900;letter-spacing:.09em;text-transform:uppercase;margin-bottom:8px;">Travel Plus</div>
                                        <div class="email-promo-title" style="font-size:20px;line-height:1.3;font-weight:900;margin-bottom:8px;">Tour, visa và MICE được thiết kế đúng mục tiêu</div>
                                        <div style="color:#cbd5e1;font-size:14px;line-height:1.65;">Đội ngũ Travel Plus sẽ tiếp nhận thông tin và phản hồi qua email hoặc số điện thoại đã cung cấp.</div>
                                        <table role="presentation" cellpadding="0" cellspacing="0" class="email-cta-table">' . $ctaHtml . '</table>
                                    </td>
                                    <td width="180" class="email-promo-image" style="background:url(' . $this->e($companyProfileUrl) . ') center/cover no-repeat;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer" style="background:#f8fcfe;border-top:1px solid #d7f0f8;padding:18px 28px;color:#64748b;font-size:13px;line-height:1.55;">
                            <strong style="color:#111827;">Travel Plus Vietnam</strong><br>
                            Hotline: ' . $this->e($contactPhoneDisplay) . ' &nbsp;|&nbsp; Email: ' . $this->e($contactEmail) . '<br>
                            Email này được gửi tự động từ hệ thống website Travel Plus.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * @param array<string, string> $details
     */
    private function renderDetails(array $details): string
    {
        if ($details === []) {
            return '';
        }

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>';
        $index = 0;

        foreach ($details as $label => $value) {
            if ($index > 0 && $index % 2 === 0) {
                $html .= '</tr><tr>';
            }

            $html .= '<td width="50%" class="email-detail-cell" style="padding:0 8px 14px 0;vertical-align:top;">
                <div class="email-detail-card" style="background:#f2fbff;border:1px solid #cdeefa;border-radius:16px;padding:14px 16px;">
                    <div style="color:#64748b;font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:6px;">' . $this->e($label) . '</div>
                    <div style="color:#07111f;font-size:16px;line-height:1.4;font-weight:800;">' . $this->e($value !== '' ? $value : '-') . '</div>
                </div>
            </td>';
            $index++;
        }

        if ($index % 2 !== 0) {
            $html .= '<td width="50%" class="email-detail-spacer" style="padding:0 0 14px 8px;">&nbsp;</td>';
        }

        return $html . '</tr></table>';
    }

    /**
     * @param array<int, array{label:string,value:string}> $rows
     */
    private function renderRows(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d9edf5;border-radius:18px;overflow:hidden;">';

        foreach ($rows as $row) {
            $html .= '<tr>
                <td width="34%" class="email-row-label" style="padding:14px 18px;background:#f8fcfe;border-bottom:1px solid #e3f2f7;color:#64748b;font-size:12px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;vertical-align:top;">' . $this->e($row['label'] ?? '') . '</td>
                <td class="email-row-value" style="padding:14px 18px;border-bottom:1px solid #e3f2f7;color:#111827;font-size:15px;line-height:1.55;font-weight:700;vertical-align:top;">' . nl2br($this->e((string) ($row['value'] ?? '-'))) . '</td>
            </tr>';
        }

        return $html . '</table>';
    }

    private function assetUrl(string $path): string
    {
        $base = trim((string) env('email.assetBaseURL', ''));

        if ($base !== '') {
            return rtrim($base, '/') . '/' . ltrim($path, '/');
        }

        return base_url($path);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
