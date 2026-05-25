<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Resolves the active application language for the current request.
 */
interface LocaleResolverInterface
{
    public function resolve(?string $requestedLanguage = null): string;

    /**
     * @return list<string>
     */
    public function supportedLanguages(): array;

    public function defaultLanguage(): string;
}
