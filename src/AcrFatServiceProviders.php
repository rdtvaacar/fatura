<?php

namespace Acr\Fat;

use Illuminate\Support\ServiceProvider;

class AcrFatServiceProviders extends ServiceProvider
{
    public function boot()
    {
        include(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/Views', 'acr_fat_v');
    }
}