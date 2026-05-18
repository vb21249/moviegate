<?php

declare(strict_types=1);

return [
    'appName' => $_ENV['APP_NAME'] ?? 'MovieGate',
    'appUrl' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'change-me',
        'ttl' => (int) ($_ENV['JWT_TTL'] ?? 3600),
        'refreshTtl' => (int) ($_ENV['JWT_REFRESH_TTL'] ?? 1209600),
        'emailVerificationTtl' => (int) ($_ENV['JWT_EMAIL_VERIFICATION_TTL'] ?? 86400),
        'passwordResetTtl' => (int) ($_ENV['JWT_PASSWORD_RESET_TTL'] ?? 3600),
    ],
    'tmdb' => [
        'baseUri' => $_ENV['TMDB_BASE_URI'] ?? 'https://api.themoviedb.org/3/',
        'apiKey' => $_ENV['TMDB_API_KEY'] ?? '',
    ],
    'soap' => [
        'endpoint' => $_ENV['SOAP_ENDPOINT'] ?? '',
        'login' => $_ENV['SOAP_LOGIN'] ?? '',
        'password' => $_ENV['SOAP_PASSWORD'] ?? '',
    ],
    'rateLimiter' => [
        'requests' => (int) ($_ENV['RATE_LIMIT_REQUESTS'] ?? 60),
        'period' => (int) ($_ENV['RATE_LIMIT_PERIOD'] ?? 60),
    ],
    'features' => [
        'registration' => filter_var($_ENV['FEATURE_REGISTRATION_ENABLED'] ?? true, FILTER_VALIDATE_BOOL),
        'recommendations' => filter_var($_ENV['FEATURE_RECOMMENDATIONS_ENABLED'] ?? false, FILTER_VALIDATE_BOOL),
    ],
];
