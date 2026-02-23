<?php

namespace Backstage\FilamentMails\Controllers;

use Backstage\FilamentMails\FilamentMailsPlugin;
use Backstage\Mails\Models\Mail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class MailPreviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(FilamentMailsPlugin::get()->userCanManageMails(), 403);

        /** @var Mail $mail */
        $mail = Mail::query()->findOrFail($request->route('mail'));

        return response($mail->html ?? '');
    }
}
