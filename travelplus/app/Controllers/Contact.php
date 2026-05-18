<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use Config\Email as EmailConfig;

class Contact extends BaseController
{
    public function index()
    {
        if ($this->request->getMethod() === 'POST') {
            return $this->submitContactForm();
        }

        return view('contact/index', $this->buildPageData());
    }

    private function submitContactForm()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $postedToken = (string) $this->request->getPost('contact_form_token');
        $sessionToken = (string) session()->get('contact_form_token');

        if ($postedToken === '' || $sessionToken === '' || ! hash_equals($sessionToken, $postedToken)) {
            return redirect()->back()->withInput()->with('error', lang('Frontend.contact.invalidToken', [], $locale));
        }

        session()->remove('contact_form_token');

        $rules = [
            'name' => 'required|min_length[2]|max_length[120]',
            'email' => 'required|valid_email|max_length[160]',
            'phone' => 'required|min_length[8]|max_length[30]',
            'destination' => 'permit_empty|max_length[160]',
            'message' => 'required|min_length[10]|max_length[5000]',
            'privacy_agree' => 'required',
            'recaptcha_token' => 'required',
        ];

        $messages = [
            'name' => [
                'required' => lang('Frontend.contact.validation.nameRequired', [], $locale),
            ],
            'email' => [
                'required' => lang('Frontend.contact.validation.emailRequired', [], $locale),
                'valid_email' => lang('Frontend.contact.validation.emailInvalid', [], $locale),
            ],
            'phone' => [
                'required' => lang('Frontend.contact.validation.phoneRequired', [], $locale),
            ],
            'message' => [
                'required' => lang('Frontend.contact.validation.messageRequired', [], $locale),
                'min_length' => lang('Frontend.contact.validation.messageMin', [], $locale),
            ],
            'privacy_agree' => [
                'required' => lang('Frontend.contact.validation.privacyRequired', [], $locale),
            ],
            'recaptcha_token' => [
                'required' => lang('Frontend.contact.validation.recaptchaRequired', [], $locale),
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        if (! $this->verifyRecaptcha((string) $this->request->getPost('recaptcha_token'))) {
            return redirect()->back()->withInput()->with('error', lang('Frontend.contact.recaptchaFailed', [], $locale));
        }

        $emailConfig = config(EmailConfig::class);
        $recipient = trim((string) env('booking.notifyEmail', $emailConfig->recipients));
        $fromEmail = trim((string) $emailConfig->fromEmail);
        $fromName = trim((string) ($emailConfig->fromName ?: 'Travel Plus'));

        if ($recipient === '' || $fromEmail === '') {
            return redirect()->back()->withInput()->with('error', lang('Frontend.contact.smtpMissing', [], $locale));
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = trim((string) $this->request->getPost('phone'));
        $destination = trim((string) $this->request->getPost('destination'));
        $message = trim((string) $this->request->getPost('message'));

        $mailer = service('email');
        $mailer->clear(true);
        $mailer->setFrom($fromEmail, $fromName);
        $mailer->setTo($recipient);
        $mailer->setReplyTo($email, $name);
        $mailer->setSubject(lang('Frontend.contact.mailSubject', [], $locale));
        $mailer->setMessage($this->buildMailBody([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'destination' => $destination,
            'message' => $message,
        ], $locale));

        if (! $mailer->send()) {
            log_message('error', 'Contact form email failed: {debug}', ['debug' => print_r($mailer->printDebugger(['headers']), true)]);

            return redirect()->back()->withInput()->with('error', lang('Frontend.contact.sendFailed', [], $locale));
        }

        return redirect()->to(LocalizedPathCatalog::url('contact', $locale))->with('success', lang('Frontend.contact.sendSuccess', [], $locale));
    }

    private function verifyRecaptcha(string $token): bool
    {
        $secretKey = '6LfgBncsAAAAAKI2vlFIqagVly-ckVVTFcGSe8lG';

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 10,
                'verify' => false,
            ]);

            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $token,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);

            return is_array($result)
                && ! empty($result['success'])
                && (($result['score'] ?? 0) >= 0.5);
        } catch (\Throwable $exception) {
            log_message('error', 'Contact form reCAPTCHA failed: {message}', ['message' => $exception->getMessage()]);

            return false;
        }
    }

    /**
     * @param array{name:string,email:string,phone:string,destination:string,message:string} $payload
     */
    private function buildMailBody(array $payload, string $locale): string
    {
        $destination = $payload['destination'] !== ''
            ? esc($payload['destination'])
            : lang('Frontend.contact.mailUnknownDestination', [], $locale);

        return '
            <h2>' . esc(lang('Frontend.contact.mailHeading', [], $locale)) . '</h2>
            <p><strong>' . esc(lang('Frontend.contact.name', [], $locale)) . ':</strong> ' . esc($payload['name']) . '</p>
            <p><strong>' . esc(lang('Frontend.contact.email', [], $locale)) . ':</strong> ' . esc($payload['email']) . '</p>
            <p><strong>' . esc(lang('Frontend.contact.phone', [], $locale)) . ':</strong> ' . esc($payload['phone']) . '</p>
            <p><strong>' . esc(lang('Frontend.contact.destination', [], $locale)) . ':</strong> ' . $destination . '</p>
            <p><strong>' . esc(lang('Frontend.contact.message', [], $locale)) . ':</strong></p>
            <div>' . nl2br(esc($payload['message'])) . '</div>
        ';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPageData(): array
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $seo = new SeoService();
        $formToken = bin2hex(random_bytes(16));
        session()->set('contact_form_token', $formToken);

        $breadcrumbs = [
            [
                'label' => lang('Frontend.common.home', [], $locale),
                'url' => localized_url('/'),
            ],
            [
                'label' => lang('Frontend.contact.breadcrumb', [], $locale),
            ],
        ];

        $metaTitle = lang('Frontend.contact.metaTitle', [], $locale);
        $metaDesc = lang('Frontend.contact.metaDesc', [], $locale);

        return [
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'canonical_url' => LocalizedPathCatalog::url('contact', $locale),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url('contact')],
                ['hreflang' => 'en', 'href' => base_url('en/contact')],
                ['hreflang' => 'x-default', 'href' => base_url('contact')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, LocalizedPathCatalog::url('contact', $locale)),
                $seo->webpageSchema($metaTitle, $metaDesc, LocalizedPathCatalog::url('contact', $locale)),
            ],
            'contact_form_token' => $formToken,
        ];
    }
}
