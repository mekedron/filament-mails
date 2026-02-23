<?php

use Backstage\FilamentMails\FilamentMailsPlugin;
use Backstage\Mails\Models\Mail;
use Backstage\Mails\Models\MailAttachment;
use Illuminate\Support\Facades\Storage;

it('returns forbidden when attachment download access is denied', function () {
    FilamentMailsPlugin::get()->canManageMails(false);

    $this->get('/admin/mails/1/attachment/1/file.txt')
        ->assertForbidden();
});

it('returns not found when attachment does not belong to the given mail', function () {
    FilamentMailsPlugin::get()->canManageMails(true);
    Storage::fake('local');

    $mail = createMail();
    $otherMail = createMail();
    $attachment = createAttachment($mail);

    $this->get("/admin/mails/{$otherMail->getKey()}/attachment/{$attachment->getKey()}/{$attachment->filename}")
        ->assertNotFound();
});

it('downloads the requested attachment for the matching mail', function () {
    FilamentMailsPlugin::get()->canManageMails(true);
    Storage::fake('local');

    $mail = createMail();
    $attachment = createAttachment($mail);

    Storage::disk('local')->put(
        "mails/attachments/{$attachment->getKey()}/{$attachment->filename}",
        'invoice-body'
    );

    $this->get("/admin/mails/{$mail->getKey()}/attachment/{$attachment->getKey()}/{$attachment->filename}")
        ->assertSuccessful()
        ->assertDownload($attachment->filename);
});

function createMail(): Mail
{
    return Mail::query()->create([
        'mailer' => 'smtp',
        'subject' => 'Mail subject',
        'html' => '<p>Mail body</p>',
        'from' => ['no-reply@example.com' => 'No Reply'],
        'to' => ['user@example.com' => 'User'],
    ]);
}

function createAttachment(Mail $mail): MailAttachment
{
    /** @var MailAttachment $attachment */
    $attachment = $mail->attachments()->create([
        'disk' => 'local',
        'uuid' => (string) str()->uuid(),
        'filename' => 'invoice.txt',
        'mime' => 'text/plain',
        'inline' => false,
        'size' => 12,
    ]);

    return $attachment;
}
