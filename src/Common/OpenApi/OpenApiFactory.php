<?php

declare(strict_types=1);

namespace App\Common\OpenApi;

final class OpenApiFactory
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $baseUrl = '/'): array
    {
        $serverUrl = $baseUrl !== '' ? $baseUrl : '/';

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'MovieGate API',
                'version' => '1.0.0',
                'description' => 'Swagger documentation for the MovieGate REST API.',
            ],
            'servers' => [
                [
                    'url' => $serverUrl,
                    'description' => 'Current application base URL',
                ],
            ],
            'tags' => [
                ['name' => 'Common', 'description' => 'Infrastructure and service status endpoints'],
                ['name' => 'Logs', 'description' => 'API logging and monitoring endpoints'],
                ['name' => 'Auth', 'description' => 'Authentication and token lifecycle endpoints'],
                ['name' => 'User', 'description' => 'Public user profile endpoints'],
                ['name' => 'Movie', 'description' => 'Movie catalog endpoints'],
                ['name' => 'Playlist', 'description' => 'Playlist management endpoints'],
                ['name' => 'Review', 'description' => 'Review CRUD endpoints'],
                ['name' => 'Rating', 'description' => 'Rating CRUD endpoints'],
                ['name' => 'Comment', 'description' => 'Comment and reply endpoints'],
                ['name' => 'Feed', 'description' => 'Activity feed endpoints'],
                ['name' => 'Recommendation', 'description' => 'Recommendation endpoints'],
                ['name' => 'Integration', 'description' => 'External integration endpoints'],
                ['name' => 'Notification', 'description' => 'Notification endpoints'],
                ['name' => 'Search', 'description' => 'Search endpoints'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
                'schemas' => $this->schemas(),
            ],
            'paths' => $this->paths(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paths(): array
    {
        return [
            '/health' => [
                'get' => $this->operation('Common', 'Healthcheck', 'Application health probe'),
            ],
            '/api/v1/logs/api' => [
                'get' => $this->operation(
                    'Logs',
                    'API logs list',
                    'Returns API request/response logs.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->stringQueryParameter('correlation_id', 'Correlation id filter'),
                            $this->stringQueryParameter('request_method', 'HTTP method filter'),
                            $this->integerQueryParameter('response_status', 'HTTP status filter'),
                            $this->stringQueryParameter('created_from', 'Start datetime filter'),
                            $this->stringQueryParameter('created_to', 'End datetime filter'),
                        ]
                    ),
                    requiresAuth: true
                ),
            ],
            '/api/v1/logs/api/summary' => [
                'get' => $this->operation(
                    'Logs',
                    'API logs summary',
                    'Returns aggregate API logging statistics.',
                    parameters: [
                        $this->stringQueryParameter('correlation_id', 'Correlation id filter'),
                        $this->stringQueryParameter('request_method', 'HTTP method filter'),
                        $this->integerQueryParameter('response_status', 'HTTP status filter'),
                        $this->stringQueryParameter('created_from', 'Start datetime filter'),
                        $this->stringQueryParameter('created_to', 'End datetime filter'),
                    ],
                    requiresAuth: true
                ),
            ],
            '/api/v1/logs/api/{id}' => [
                'get' => $this->operation(
                    'Logs',
                    'API log details',
                    'Returns a single API log entry.',
                    parameters: [$this->pathIdParameter('id', 'Log entry id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/auth/signup' => [
                'post' => $this->operation(
                    'Auth',
                    'Sign up',
                    'Creates a new user account.',
                    requestSchema: '#/components/schemas/SignupRequest',
                    successStatus: 201
                ),
            ],
            '/api/v1/auth/login' => [
                'post' => $this->operation(
                    'Auth',
                    'Login',
                    'Authenticates a user and returns token payload.',
                    requestSchema: '#/components/schemas/LoginRequest'
                ),
            ],
            '/api/v1/auth/logout' => [
                'post' => $this->operation(
                    'Auth',
                    'Logout',
                    'Invalidates a refresh token.',
                    requestSchema: '#/components/schemas/RefreshTokenPayload'
                ),
            ],
            '/api/v1/auth/refresh' => [
                'post' => $this->operation(
                    'Auth',
                    'Refresh token',
                    'Rotates access and refresh tokens.',
                    requestSchema: '#/components/schemas/RefreshTokenPayload'
                ),
            ],
            '/api/v1/auth/verify-email' => [
                'post' => $this->operation(
                    'Auth',
                    'Verify email',
                    'Confirms email ownership by verification token.',
                    requestSchema: '#/components/schemas/VerifyEmailRequest'
                ),
            ],
            '/api/v1/auth/request-password-reset' => [
                'post' => $this->operation(
                    'Auth',
                    'Request password reset',
                    'Creates a password reset token.',
                    requestSchema: '#/components/schemas/RequestPasswordResetRequest'
                ),
            ],
            '/api/v1/auth/reset-password' => [
                'post' => $this->operation(
                    'Auth',
                    'Reset password',
                    'Completes password reset by token.',
                    requestSchema: '#/components/schemas/ResetPasswordRequest'
                ),
            ],
            '/api/v1/users/{id}' => [
                'get' => $this->operation(
                    'User',
                    'User details',
                    'Returns a public user profile.',
                    parameters: [$this->pathIdParameter('id', 'User id')]
                ),
            ],
            '/api/v1/users/{id}/activity' => [
                'get' => $this->operation(
                    'User',
                    'User activity',
                    'Returns a user activity feed snapshot.',
                    parameters: array_merge([$this->pathIdParameter('id', 'User id')], $this->limitOffsetParameters())
                ),
            ],
            '/api/v1/users/{id}/ratings' => [
                'get' => $this->operation(
                    'User',
                    'User recent ratings',
                    'Returns recent ratings for a user.',
                    parameters: array_merge([$this->pathIdParameter('id', 'User id')], $this->limitOffsetParameters())
                ),
            ],
            '/api/v1/users/{id}/posts' => [
                'get' => $this->operation(
                    'User',
                    'User posts',
                    'Returns recent authored content for a user.',
                    parameters: array_merge([$this->pathIdParameter('id', 'User id')], $this->limitOffsetParameters())
                ),
            ],
            '/api/v1/movies' => [
                'get' => $this->operation(
                    'Movie',
                    'Movie list',
                    'Returns movie catalog items.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->stringQueryParameter('q', 'Search phrase'),
                            $this->stringQueryParameter('language', 'Locale, for example en-US'),
                            $this->stringQueryParameter('lang', 'Alternative locale parameter, for example en-US'),
                        ]
                    )
                ),
            ],
            '/api/v1/movies/{id}' => [
                'get' => $this->operation(
                    'Movie',
                    'Movie details',
                    'Returns movie details.',
                    parameters: [
                        $this->pathIdParameter('id', 'Movie id'),
                        $this->stringQueryParameter('language', 'Locale, for example en-US'),
                        $this->stringQueryParameter('lang', 'Alternative locale parameter, for example en-US'),
                    ]
                ),
            ],
            '/api/v1/movies/{id}/watch' => [
                'post' => $this->operation(
                    'Movie',
                    'Mark movie as watched',
                    'Marks a movie as watched for the current user.',
                    parameters: [
                        $this->pathIdParameter('id', 'Movie id'),
                        $this->stringQueryParameter('language', 'Locale, for example en-US'),
                        $this->stringQueryParameter('lang', 'Alternative locale parameter, for example en-US'),
                    ],
                    requiresAuth: true
                ),
            ],
            '/api/v1/playlists' => [
                'get' => $this->operation(
                    'Playlist',
                    'Playlist list',
                    'Returns playlists visible to the current user.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->stringQueryParameter('q', 'Search phrase'),
                            $this->stringQueryParameter('visibility', 'Playlist visibility'),
                        ]
                    ),
                    requiresAuth: true
                ),
                'post' => $this->operation(
                    'Playlist',
                    'Create playlist',
                    'Creates a playlist.',
                    requestSchema: '#/components/schemas/PlaylistCreateRequest',
                    successStatus: 201,
                    requiresAuth: true
                ),
            ],
            '/api/v1/playlists/{id}' => [
                'get' => $this->operation(
                    'Playlist',
                    'Playlist details',
                    'Returns a playlist by id.',
                    parameters: [$this->pathIdParameter('id', 'Playlist id')],
                    requiresAuth: true
                ),
                'patch' => $this->operation(
                    'Playlist',
                    'Update playlist',
                    'Updates a playlist.',
                    parameters: [$this->pathIdParameter('id', 'Playlist id')],
                    requestSchema: '#/components/schemas/PlaylistUpdateRequest',
                    requiresAuth: true
                ),
                'delete' => $this->operation(
                    'Playlist',
                    'Delete playlist',
                    'Deletes a playlist.',
                    parameters: [$this->pathIdParameter('id', 'Playlist id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/playlists/{id}/movies' => [
                'post' => $this->operation(
                    'Playlist',
                    'Add movie to playlist',
                    'Adds a movie to a playlist.',
                    parameters: [$this->pathIdParameter('id', 'Playlist id')],
                    requestSchema: '#/components/schemas/PlaylistMovieRequest',
                    successStatus: 201,
                    requiresAuth: true
                ),
            ],
            '/api/v1/playlists/{id}/movies/{movieId}' => [
                'delete' => $this->operation(
                    'Playlist',
                    'Remove movie from playlist',
                    'Removes a movie from a playlist.',
                    parameters: [
                        $this->pathIdParameter('id', 'Playlist id'),
                        $this->pathIdParameter('movieId', 'Movie id'),
                    ],
                    requiresAuth: true
                ),
            ],
            '/api/v1/reviews' => [
                'get' => $this->operation(
                    'Review',
                    'Review list',
                    'Returns reviews with filtering options.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->stringQueryParameter('q', 'Search phrase'),
                            $this->integerQueryParameter('movie_id', 'Movie id filter'),
                            $this->integerQueryParameter('user_id', 'User id filter'),
                        ]
                    )
                ),
                'post' => $this->operation(
                    'Review',
                    'Create review',
                    'Creates a review.',
                    requestSchema: '#/components/schemas/ReviewCreateRequest',
                    successStatus: 201,
                    requiresAuth: true
                ),
            ],
            '/api/v1/reviews/{id}' => [
                'get' => $this->operation(
                    'Review',
                    'Review details',
                    'Returns a review by id.',
                    parameters: [$this->pathIdParameter('id', 'Review id')]
                ),
                'patch' => $this->operation(
                    'Review',
                    'Update review',
                    'Updates a review.',
                    parameters: [$this->pathIdParameter('id', 'Review id')],
                    requestSchema: '#/components/schemas/ReviewUpdateRequest',
                    requiresAuth: true
                ),
                'delete' => $this->operation(
                    'Review',
                    'Delete review',
                    'Deletes a review.',
                    parameters: [$this->pathIdParameter('id', 'Review id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/ratings' => [
                'get' => $this->operation(
                    'Rating',
                    'Rating list',
                    'Returns ratings with filtering options.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->integerQueryParameter('movie_id', 'Movie id filter'),
                            $this->integerQueryParameter('user_id', 'User id filter'),
                            $this->integerQueryParameter('score', 'Score filter'),
                        ]
                    )
                ),
                'post' => $this->operation(
                    'Rating',
                    'Create rating',
                    'Creates a rating.',
                    requestSchema: '#/components/schemas/RatingCreateRequest',
                    requiresAuth: true
                ),
            ],
            '/api/v1/ratings/history' => [
                'get' => $this->operation(
                    'Rating',
                    'Rating history',
                    'Returns rating history for the current user.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [$this->integerQueryParameter('movie_id', 'Movie id filter')]
                    ),
                    requiresAuth: true
                ),
            ],
            '/api/v1/ratings/{id}' => [
                'get' => $this->operation(
                    'Rating',
                    'Rating details',
                    'Returns a rating by id.',
                    parameters: [$this->pathIdParameter('id', 'Rating id')]
                ),
                'patch' => $this->operation(
                    'Rating',
                    'Update rating',
                    'Updates a rating.',
                    parameters: [$this->pathIdParameter('id', 'Rating id')],
                    requestSchema: '#/components/schemas/RatingUpdateRequest',
                    requiresAuth: true
                ),
                'delete' => $this->operation(
                    'Rating',
                    'Delete rating',
                    'Deletes a rating.',
                    parameters: [$this->pathIdParameter('id', 'Rating id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/comments' => [
                'get' => $this->operation(
                    'Comment',
                    'Comment list',
                    'Returns comments with filtering options.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->integerQueryParameter('movie_id', 'Movie id filter'),
                            $this->integerQueryParameter('review_id', 'Review id filter'),
                            $this->integerQueryParameter('user_id', 'User id filter'),
                            $this->integerQueryParameter('parent_id', 'Parent comment id filter'),
                        ]
                    )
                ),
                'post' => $this->operation(
                    'Comment',
                    'Create comment',
                    'Creates a top-level comment.',
                    requestSchema: '#/components/schemas/CommentCreateRequest',
                    successStatus: 201,
                    requiresAuth: true
                ),
            ],
            '/api/v1/comments/{id}' => [
                'get' => $this->operation(
                    'Comment',
                    'Comment details',
                    'Returns a comment by id.',
                    parameters: [$this->pathIdParameter('id', 'Comment id')]
                ),
                'patch' => $this->operation(
                    'Comment',
                    'Update comment',
                    'Updates a comment body.',
                    parameters: [$this->pathIdParameter('id', 'Comment id')],
                    requestSchema: '#/components/schemas/CommentUpdateRequest',
                    requiresAuth: true
                ),
                'delete' => $this->operation(
                    'Comment',
                    'Delete comment',
                    'Deletes a comment.',
                    parameters: [$this->pathIdParameter('id', 'Comment id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/comments/{id}/replies' => [
                'post' => $this->operation(
                    'Comment',
                    'Reply to comment',
                    'Creates a reply for the given parent comment.',
                    parameters: [$this->pathIdParameter('id', 'Parent comment id')],
                    requestSchema: '#/components/schemas/CommentReplyRequest',
                    successStatus: 201,
                    requiresAuth: true
                ),
            ],
            '/api/v1/comments/{id}/like' => [
                'post' => $this->operation(
                    'Comment',
                    'Like comment',
                    'Adds a like to a comment for the current user.',
                    parameters: [$this->pathIdParameter('id', 'Comment id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/feed' => [
                'get' => $this->operation(
                    'Feed',
                    'Feed list',
                    'Returns the public activity feed.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->integerQueryParameter('user_id', 'User id filter'),
                            $this->integerQueryParameter('entity_id', 'Entity id filter'),
                            $this->stringQueryParameter('event_type', 'Event type filter'),
                            $this->stringQueryParameter('entity_type', 'Entity type filter'),
                        ]
                    )
                ),
            ],
            '/api/v1/feed/me' => [
                'get' => $this->operation(
                    'Feed',
                    'My feed',
                    'Returns the current user activity feed.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->integerQueryParameter('entity_id', 'Entity id filter'),
                            $this->stringQueryParameter('event_type', 'Event type filter'),
                            $this->stringQueryParameter('entity_type', 'Entity type filter'),
                        ]
                    ),
                    requiresAuth: true
                ),
            ],
            '/api/v1/feed/{id}' => [
                'get' => $this->operation(
                    'Feed',
                    'Feed item details',
                    'Returns a feed item by id.',
                    parameters: [$this->pathIdParameter('id', 'Feed item id')]
                ),
            ],
            '/api/v1/recommendations' => [
                'get' => $this->operation(
                    'Recommendation',
                    'Recommendation list',
                    'Returns recommendations. Authentication is optional for this endpoint.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(50),
                        [
                            $this->stringQueryParameter('source', 'Recommendation source'),
                            $this->booleanQueryParameter('refresh', 'Force refresh'),
                        ]
                    )
                ),
            ],
            '/api/v1/recommendations/rebuild' => [
                'post' => $this->operation(
                    'Recommendation',
                    'Rebuild recommendations',
                    'Triggers recommendations rebuild.',
                    requestSchema: '#/components/schemas/RecommendationRebuildRequest',
                    requiresAuth: true
                ),
            ],
            '/api/v1/integrations/tmdb/sync' => [
                'post' => $this->operation(
                    'Integration',
                    'TMDB sync',
                    'Synchronizes movie data from TMDB.',
                    requestSchema: '#/components/schemas/TmdbSyncRequest',
                    requiresAuth: true
                ),
            ],
            '/api/v1/notifications' => [
                'get' => $this->operation(
                    'Notification',
                    'Notification list',
                    'Returns notifications for the current user.',
                    parameters: array_merge(
                        $this->limitOffsetParameters(),
                        [
                            $this->stringQueryParameter('type', 'Notification type filter'),
                            $this->booleanQueryParameter('unread_only', 'Return unread notifications only'),
                        ]
                    ),
                    requiresAuth: true
                ),
            ],
            '/api/v1/notifications/{id}' => [
                'get' => $this->operation(
                    'Notification',
                    'Notification details',
                    'Returns a notification by id.',
                    parameters: [$this->pathIdParameter('id', 'Notification id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/notifications/read-all' => [
                'patch' => $this->operation(
                    'Notification',
                    'Mark all notifications as read',
                    'Marks all notifications as read, optionally by type.',
                    requestSchema: '#/components/schemas/NotificationMarkAllReadRequest',
                    requiresAuth: true
                ),
            ],
            '/api/v1/notifications/{id}/read' => [
                'patch' => $this->operation(
                    'Notification',
                    'Mark notification as read',
                    'Marks one notification as read.',
                    parameters: [$this->pathIdParameter('id', 'Notification id')],
                    requiresAuth: true
                ),
            ],
            '/api/v1/search' => [
                'get' => $this->operation(
                    'Search',
                    'Search',
                    'Returns search results.',
                    parameters: [
                        $this->stringQueryParameter('q', 'Search phrase'),
                        $this->limitQueryParameter(),
                        $this->offsetQueryParameter(),
                    ]
                ),
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $parameters
     * @return array<string, mixed>
     */
    private function operation(
        string $tag,
        string $summary,
        string $description,
        array $parameters = [],
        ?string $requestSchema = null,
        int $successStatus = 200,
        bool $requiresAuth = false,
    ): array {
        $operation = [
            'tags' => [$tag],
            'summary' => $summary,
            'description' => $description,
            'responses' => [
                (string) $successStatus => $this->successResponse($successStatus),
                '401' => $this->errorResponse('Authentication required or bearer token is invalid.'),
                '403' => $this->errorResponse('Authenticated user does not have enough permissions.', 403),
                '404' => $this->errorResponse('Requested resource was not found.', 404),
                '422' => $this->errorResponse('Request validation failed.', 422),
            ],
        ];

        if ($parameters !== []) {
            $operation['parameters'] = $parameters;
        }

        if ($requestSchema !== null) {
            $operation['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => $requestSchema,
                        ],
                    ],
                ],
            ];
        }

        if ($requiresAuth) {
            $operation['security'] = [
                ['bearerAuth' => []],
            ];
        }

        return $operation;
    }

    /**
     * @return array<string, mixed>
     */
    private function successResponse(int $status): array
    {
        return [
            'description' => $status === 201 ? 'Created' : 'Successful response',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ApiSuccessEnvelope',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResponse(string $description, int $status = 401): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ApiErrorEnvelope',
                    ],
                    'examples' => [
                        'default' => [
                            'value' => [
                                'success' => false,
                                'error' => [
                                    'code' => match ($status) {
                                        401 => 'unauthorized',
                                        403 => 'forbidden',
                                        404 => 'not_found',
                                        422 => 'validation_error',
                                        default => 'error',
                                    },
                                    'message' => $description,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function schemas(): array
    {
        return [
            'ApiSuccessEnvelope' => [
                'type' => 'object',
                'required' => ['success'],
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'data' => [
                        'nullable' => true,
                        'description' => 'Endpoint-specific payload.',
                    ],
                    'meta' => [
                        'type' => 'object',
                        'nullable' => true,
                        'additionalProperties' => true,
                        'description' => 'Optional metadata.',
                    ],
                ],
            ],
            'ApiErrorEnvelope' => [
                'type' => 'object',
                'required' => ['success', 'error'],
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'error' => [
                        '$ref' => '#/components/schemas/ApiErrorPayload',
                    ],
                ],
            ],
            'ApiErrorPayload' => [
                'type' => 'object',
                'required' => ['code', 'message'],
                'properties' => [
                    'code' => ['type' => 'string', 'example' => 'validation_error'],
                    'message' => ['type' => 'string', 'example' => 'Validation failed.'],
                ],
            ],
            'SignupRequest' => [
                'type' => 'object',
                'required' => ['email', 'username', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 190],
                    'username' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 100],
                    'password' => ['type' => 'string', 'minLength' => 8, 'maxLength' => 255],
                ],
            ],
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'minLength' => 8, 'maxLength' => 255],
                ],
            ],
            'RefreshTokenPayload' => [
                'type' => 'object',
                'required' => ['refresh_token'],
                'properties' => [
                    'refresh_token' => ['type' => 'string', 'maxLength' => 2048],
                ],
            ],
            'VerifyEmailRequest' => [
                'type' => 'object',
                'required' => ['token'],
                'properties' => [
                    'token' => ['type' => 'string', 'maxLength' => 2048],
                ],
            ],
            'RequestPasswordResetRequest' => [
                'type' => 'object',
                'required' => ['email'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 190],
                ],
            ],
            'ResetPasswordRequest' => [
                'type' => 'object',
                'required' => ['token', 'newPassword'],
                'properties' => [
                    'token' => ['type' => 'string', 'maxLength' => 2048],
                    'newPassword' => ['type' => 'string', 'minLength' => 8, 'maxLength' => 255],
                ],
            ],
            'PlaylistCreateRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                    'slug' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 190],
                    'description' => ['type' => 'string', 'maxLength' => 5000],
                    'visibility' => ['type' => 'string', 'description' => 'See PlaylistVisibility enum in the domain layer.'],
                    'is_default_watch_later' => ['type' => 'boolean'],
                ],
            ],
            'PlaylistUpdateRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                    'slug' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 190],
                    'description' => ['type' => 'string', 'maxLength' => 5000],
                    'visibility' => ['type' => 'string', 'description' => 'See PlaylistVisibility enum in the domain layer.'],
                    'is_default_watch_later' => ['type' => 'boolean'],
                ],
            ],
            'PlaylistMovieRequest' => [
                'type' => 'object',
                'required' => ['movie_id'],
                'properties' => [
                    'movie_id' => ['type' => 'integer', 'minimum' => 1],
                    'position' => ['type' => 'integer', 'minimum' => 0, 'nullable' => true],
                ],
            ],
            'ReviewCreateRequest' => [
                'type' => 'object',
                'required' => ['movie_id', 'title', 'body'],
                'properties' => [
                    'movie_id' => ['type' => 'integer', 'minimum' => 1],
                    'rating_id' => ['type' => 'integer', 'minimum' => 1, 'nullable' => true],
                    'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                    'body' => ['type' => 'string', 'minLength' => 10, 'maxLength' => 20000],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                ],
            ],
            'ReviewUpdateRequest' => [
                'type' => 'object',
                'properties' => [
                    'rating_id' => ['type' => 'integer', 'minimum' => 1, 'nullable' => true],
                    'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                    'body' => ['type' => 'string', 'minLength' => 10, 'maxLength' => 20000],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                ],
            ],
            'RatingCreateRequest' => [
                'type' => 'object',
                'required' => ['movie_id', 'score'],
                'properties' => [
                    'movie_id' => ['type' => 'integer', 'minimum' => 1],
                    'score' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                    'review_text' => ['type' => 'string', 'maxLength' => 5000, 'nullable' => true],
                ],
            ],
            'RatingUpdateRequest' => [
                'type' => 'object',
                'properties' => [
                    'score' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                    'review_text' => ['type' => 'string', 'maxLength' => 5000, 'nullable' => true],
                ],
            ],
            'CommentCreateRequest' => [
                'type' => 'object',
                'required' => ['body'],
                'properties' => [
                    'movie_id' => ['type' => 'integer', 'minimum' => 1, 'nullable' => true],
                    'review_id' => ['type' => 'integer', 'minimum' => 1, 'nullable' => true],
                    'body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 5000],
                ],
            ],
            'CommentReplyRequest' => [
                'type' => 'object',
                'required' => ['body'],
                'properties' => [
                    'body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 5000],
                ],
            ],
            'CommentUpdateRequest' => [
                'type' => 'object',
                'required' => ['body'],
                'properties' => [
                    'body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 5000],
                ],
            ],
            'RecommendationRebuildRequest' => [
                'type' => 'object',
                'properties' => [
                    'source' => ['type' => 'string', 'enum' => ['personalized', 'popular', 'recent'], 'nullable' => true],
                ],
            ],
            'TmdbSyncRequest' => [
                'type' => 'object',
                'properties' => [
                    'tmdb_id' => ['type' => 'integer', 'minimum' => 1, 'nullable' => true],
                    'query' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 190, 'nullable' => true],
                    'language' => ['type' => 'string', 'pattern' => '^[a-z]{2}-[A-Z]{2}$', 'default' => 'en-US'],
                    'include_adult' => ['type' => 'boolean', 'default' => false],
                ],
                'description' => 'Either tmdb_id or query is required.',
            ],
            'NotificationMarkAllReadRequest' => [
                'type' => 'object',
                'properties' => [
                    'type' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100, 'nullable' => true],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function limitOffsetParameters(int $maxLimit = 100): array
    {
        return [
            $this->limitQueryParameter($maxLimit),
            $this->offsetQueryParameter(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function limitQueryParameter(int $maxLimit = 100): array
    {
        return [
            'name' => 'limit',
            'in' => 'query',
            'required' => false,
            'description' => 'Maximum number of items to return.',
            'schema' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => $maxLimit,
                'default' => 20,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function offsetQueryParameter(): array
    {
        return [
            'name' => 'offset',
            'in' => 'query',
            'required' => false,
            'description' => 'Zero-based offset.',
            'schema' => [
                'type' => 'integer',
                'minimum' => 0,
                'default' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pathIdParameter(string $name, string $description): array
    {
        return [
            'name' => $name,
            'in' => 'path',
            'required' => true,
            'description' => $description,
            'schema' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stringQueryParameter(string $name, string $description): array
    {
        return [
            'name' => $name,
            'in' => 'query',
            'required' => false,
            'description' => $description,
            'schema' => [
                'type' => 'string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function integerQueryParameter(string $name, string $description): array
    {
        return [
            'name' => $name,
            'in' => 'query',
            'required' => false,
            'description' => $description,
            'schema' => [
                'type' => 'integer',
                'minimum' => 1,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function booleanQueryParameter(string $name, string $description): array
    {
        return [
            'name' => $name,
            'in' => 'query',
            'required' => false,
            'description' => $description,
            'schema' => [
                'type' => 'boolean',
            ],
        ];
    }
}
