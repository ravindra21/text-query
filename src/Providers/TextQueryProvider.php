<?php
namespace Ravindra21\TextQuery\Providers;

use Illuminate\Support\ServiceProvider;

class TextQueryProvider extends ServiceProvider {
    public function boot() {
        $this->publishes([
            __DIR__.'/../config/text_query.php' => config_path('text_query.php')
        ], 'text-query');
    }
}