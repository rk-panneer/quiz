<?php

namespace App\Services\LLM;

use App\Services\LLM\Contracts\LLMProviderInterface;
use App\Services\LLM\Drivers\GeminiLLMProvider;
use Illuminate\Support\Manager;

class LLMManager extends Manager
{
    public function getDefaultDriver()
    {
        return config('llm.default');
    }

    public function createGeminiDriver(): LLMProviderInterface
    {
        return new GeminiLLMProvider();
    }
}
