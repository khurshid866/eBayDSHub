<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyAdminWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Company $company;
    public User $user;
    public string $plainPassword;
    public string $loginUrl;

    public function __construct(Company $company, User $user, string $plainPassword)
    {
        $this->company = $company;
        $this->user = $user;
        $this->plainPassword = $plainPassword;

        $baseUrl = config('app.url');
        if (empty($baseUrl) || str_contains($baseUrl, 'localhost')) {
            $baseUrl = request()->schemeAndHttpHost();
        }
        if (empty($baseUrl) || str_contains($baseUrl, 'localhost')) {
            $baseUrl = 'https://ebay.luxconvo.com';
        }
        $this->loginUrl = rtrim($baseUrl, '/') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to eBay Dropshipping Hub - Company Admin Access Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company_admin_welcome',
        );
    }
}
