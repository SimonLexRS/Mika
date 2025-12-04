<?php

namespace App\Providers;

use App\Services\ChatBrain\ChatBrainService;
use App\Services\ChatBrain\Extractors\AmountExtractor;
use App\Services\ChatBrain\Extractors\CategoryExtractor;
use App\Services\ChatBrain\Extractors\DateExtractor;
use App\Services\ChatBrain\IntentDetector;
use App\Services\ChatBrain\Intents\GreetingIntent;
use App\Services\ChatBrain\Intents\HelpIntent;
use App\Services\ChatBrain\Intents\QueryBalanceIntent;
use App\Services\ChatBrain\Intents\QueryTransactionsIntent;
use App\Services\ChatBrain\Intents\RegisterExpenseIntent;
use App\Services\ChatBrain\Intents\RegisterIncomeIntent;
use Illuminate\Support\ServiceProvider;

class ChatBrainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar IntentDetector como singleton
        $this->app->singleton(IntentDetector::class, function () {
            return new IntentDetector();
        });

        // Registrar ChatBrainService como singleton
        $this->app->singleton(ChatBrainService::class, function ($app) {
            return new ChatBrainService(
                intentDetector: $app->make(IntentDetector::class),
                handlers: [
                    new RegisterExpenseIntent(),
                    new RegisterIncomeIntent(),
                    new QueryBalanceIntent(),
                    new QueryTransactionsIntent(),
                    new GreetingIntent(),
                    new HelpIntent(),
                ],
                extractors: [
                    'amount' => new AmountExtractor(),
                    'category' => new CategoryExtractor(),
                    'date' => new DateExtractor(),
                ]
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
