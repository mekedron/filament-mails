<?php

use Backstage\FilamentMails\FilamentMailsPlugin;
use Backstage\Mails\Models\Mail;

it('returns forbidden when mail access is denied', function () {
    FilamentMailsPlugin::get()->canManageMails(false);

    $this->get('/admin/mails/1/preview')
        ->assertForbidden();
});

it('returns not found for unknown mails', function () {
    FilamentMailsPlugin::get()->canManageMails(true);

    $this->get('/admin/mails/999/preview')
        ->assertNotFound();
});

it('renders the stored html preview for an existing mail', function () {
    FilamentMailsPlugin::get()->canManageMails(true);

    $mail = Mail::query()->create([
        'mailer' => 'smtp',
        'subject' => 'Test subject',
        'html' => '<h1>Preview</h1><p>Hello</p>',
        'from' => ['no-reply@example.com' => 'No Reply'],
        'to' => ['user@example.com' => 'User'],
    ]);

    $this->get("/admin/mails/{$mail->getKey()}/preview")
        ->assertSuccessful()
        ->assertSee('<h1>Preview</h1>', false);
});
