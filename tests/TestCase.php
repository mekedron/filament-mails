<?php

namespace Backstage\FilamentMails\Tests;

use Backstage\FilamentMails\Facades\FilamentMails as FilamentMailsRoutes;
use Backstage\FilamentMails\FilamentMailsPlugin;
use Backstage\FilamentMails\FilamentMailsServiceProvider;
use Backstage\Mails\MailsServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Panel;
use Filament\PanelRegistry;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Backstage\\FilamentMails\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        $this->setUpMailTables();
        $this->setUpFilamentPanel();
        $this->setUpFilamentMailRoutes();
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            MailsServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentMailsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function setUpMailTables(): void
    {
        if (! Schema::hasTable('mails')) {
            Schema::create('mails', function (Blueprint $table): void {
                $table->id();
                $table->string('uuid')->nullable();
                $table->string('mailer')->nullable();
                $table->string('transport')->nullable();
                $table->string('stream_id')->nullable();
                $table->string('mail_class')->nullable();
                $table->string('subject')->nullable();
                $table->longText('html')->nullable();
                $table->longText('text')->nullable();
                $table->json('from')->nullable();
                $table->json('reply_to')->nullable();
                $table->json('to')->nullable();
                $table->json('cc')->nullable();
                $table->json('bcc')->nullable();
                $table->unsignedBigInteger('opens')->default(0);
                $table->unsignedBigInteger('clicks')->default(0);
                $table->json('tags')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('resent_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('last_opened_at')->nullable();
                $table->timestamp('last_clicked_at')->nullable();
                $table->timestamp('complained_at')->nullable();
                $table->timestamp('soft_bounced_at')->nullable();
                $table->timestamp('hard_bounced_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mail_attachments')) {
            Schema::create('mail_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('mail_id')->constrained('mails')->cascadeOnDelete();
                $table->string('disk');
                $table->string('uuid');
                $table->string('filename');
                $table->string('mime');
                $table->boolean('inline')->default(false);
                $table->bigInteger('size');
                $table->timestamps();
            });
        }
    }

    protected function setUpFilamentPanel(): void
    {
        $panel = Panel::make()
            ->id('admin')
            ->path('admin')
            ->default()
            ->plugin(FilamentMailsPlugin::make());

        app(PanelRegistry::class)->register($panel);
        Filament::setCurrentPanel($panel);
    }

    protected function setUpFilamentMailRoutes(): void
    {
        Route::name('filament.admin.')
            ->prefix('admin')
            ->group(function (): void {
                FilamentMailsRoutes::routes();
            });
    }
}
