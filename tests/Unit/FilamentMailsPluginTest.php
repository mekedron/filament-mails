<?php

use Backstage\FilamentMails\FilamentMailsPlugin;

it('exposes the expected plugin id', function () {
    expect((new FilamentMailsPlugin)->getId())
        ->toBe('filament-mails');
});

it('evaluates boolean and closure mail access settings', function () {
    $plugin = new FilamentMailsPlugin;

    $plugin->canManageMails(false);
    expect($plugin->userCanManageMails())->toBeFalse();

    $plugin->canManageMails(fn (): bool => true);
    expect($plugin->userCanManageMails())->toBeTrue();
});
