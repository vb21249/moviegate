<?php

declare(strict_types=1);

namespace App\Common\Localization;

use App\Common\Contracts\LocaleResolverInterface;
use Yii;

/**
 * Resolves locale from explicit request input, Accept-Language, then config default.
 */
final class RequestLocaleResolver implements LocaleResolverInterface
{
    public function resolve(?string $requestedLanguage = null): string
    {
        foreach ($this->candidates($requestedLanguage) as $candidate) {
            $language = $this->matchSupportedLanguage($candidate);

            if ($language !== null) {
                return $language;
            }
        }

        return $this->defaultLanguage();
    }

    public function supportedLanguages(): array
    {
        $languages = Yii::$app !== null ? (Yii::$app->params['i18n']['supportedLanguages'] ?? ['en-US']) : ['en-US'];
        $languages = is_array($languages) ? $languages : ['en-US'];
        $normalized = [];

        foreach ($languages as $language) {
            $language = $this->normalizeLanguage((string) $language);

            if ($language !== null) {
                $normalized[] = $language;
            }
        }

        return $normalized !== [] ? array_values(array_unique($normalized)) : ['en-US'];
    }

    public function defaultLanguage(): string
    {
        $default = $this->normalizeLanguage((string) (Yii::$app !== null ? (Yii::$app->params['i18n']['defaultLanguage'] ?? 'en-US') : 'en-US'));

        return $default ?? 'en-US';
    }

    /**
     * @return list<string>
     */
    private function candidates(?string $requestedLanguage): array
    {
        $candidates = [];

        if ($requestedLanguage !== null && trim($requestedLanguage) !== '') {
            $candidates[] = $requestedLanguage;
        }

        if (Yii::$app !== null && Yii::$app instanceof \yii\web\Application) {
            $request = Yii::$app->request;
            $queryLanguage = $request->get('language', $request->get('lang'));

            if (is_string($queryLanguage) && trim($queryLanguage) !== '') {
                $candidates[] = $queryLanguage;
            }

            $acceptLanguage = $request->headers->get('Accept-Language');

            if (is_string($acceptLanguage) && trim($acceptLanguage) !== '') {
                array_push($candidates, ...$this->parseAcceptLanguage($acceptLanguage));
            }
        }

        $candidates[] = $this->defaultLanguage();

        return $candidates;
    }

    /**
     * @return list<string>
     */
    private function parseAcceptLanguage(string $header): array
    {
        $languages = [];

        foreach (explode(',', $header) as $part) {
            $language = trim(explode(';', $part)[0] ?? '');

            if ($language !== '') {
                $languages[] = $language;
            }
        }

        return $languages;
    }

    private function matchSupportedLanguage(string $candidate): ?string
    {
        $candidate = $this->normalizeLanguage($candidate);

        if ($candidate === null) {
            return null;
        }

        foreach ($this->supportedLanguages() as $supportedLanguage) {
            if (strcasecmp($supportedLanguage, $candidate) === 0) {
                return $supportedLanguage;
            }
        }

        $candidatePrefix = strtolower(substr($candidate, 0, 2));

        foreach ($this->supportedLanguages() as $supportedLanguage) {
            if (strtolower(substr($supportedLanguage, 0, 2)) === $candidatePrefix) {
                return $supportedLanguage;
            }
        }

        return null;
    }

    private function normalizeLanguage(string $language): ?string
    {
        $language = trim(str_replace('_', '-', $language));

        if (!preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/i', $language)) {
            return null;
        }

        $parts = explode('-', $language);
        $primary = strtolower($parts[0]);

        return isset($parts[1]) ? $primary . '-' . strtoupper($parts[1]) : $primary;
    }
}
