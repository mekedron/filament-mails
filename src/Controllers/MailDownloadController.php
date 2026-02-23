<?php

namespace Backstage\FilamentMails\Controllers;

use Backstage\FilamentMails\FilamentMailsPlugin;
use Backstage\Mails\Models\MailAttachment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class MailDownloadController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless(FilamentMailsPlugin::get()->userCanManageMails(), 403);

        $mailId = $request->route('mail');
        $attachmentId = $request->route('attachment');

        /** @var class-string<MailAttachment> $attachmentModel */
        $attachmentModel = Config::get('mails.models.attachment');

        /** @var MailAttachment $attachment */
        $attachment = $attachmentModel::query()
            ->whereKey($attachmentId)
            ->where('mail_id', $mailId)
            ->firstOrFail();

        return Storage::disk($attachment->disk)->download(
            $attachment->storagePath,
            $attachment->filename,
            ['Content-Type' => $attachment->mime]
        );
    }
}
