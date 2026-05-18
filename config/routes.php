<?php

declare(strict_types=1);

return [
    'GET health' => 'common/api/v1/health/index',
    'POST api/v1/auth/signup' => 'auth/api/v1/auth/signup',
    'POST api/v1/auth/login' => 'auth/api/v1/auth/login',
    'POST api/v1/auth/logout' => 'auth/api/v1/auth/logout',
    'POST api/v1/auth/refresh' => 'auth/api/v1/auth/refresh',
    'GET api/v1/users/<id:\d+>' => 'user/api/v1/user/view',
    'GET api/v1/users/<id:\d+>/activity' => 'user/api/v1/user/activity',
    'GET api/v1/users/<id:\d+>/ratings' => 'user/api/v1/user/recent-ratings',
    'GET api/v1/users/<id:\d+>/posts' => 'user/api/v1/user/posts',
    'GET api/v1/movies' => 'movie/api/v1/movie/index',
    'GET api/v1/movies/<id:\d+>' => 'movie/api/v1/movie/view',
    'POST api/v1/movies/<id:\d+>/watch' => 'movie/api/v1/movie/watch',
    'GET api/v1/playlists' => 'playlist/api/v1/playlist/index',
    'POST api/v1/playlists' => 'playlist/api/v1/playlist/create',
    'GET api/v1/reviews' => 'review/api/v1/review/index',
    'POST api/v1/ratings' => 'rating/api/v1/rating/create',
    'GET api/v1/comments' => 'comment/api/v1/comment/index',
    'GET api/v1/feed' => 'feed/api/v1/feed/index',
    'GET api/v1/recommendations' => 'recommendation/api/v1/recommendation/index',
    'POST api/v1/integrations/tmdb/sync' => 'integration/api/v1/integration/tmdb-sync',
    'GET api/v1/notifications' => 'notification/api/v1/notification/index',
    'GET api/v1/search' => 'search/api/v1/search/index',
    'POST api/v1/auth/verify-email' => 'auth/auth/verify-email',
    'POST api/v1/auth/request-password-reset' => 'auth/auth/request-password-reset',
    'POST api/v1/auth/reset-password' => 'auth/auth/reset-password',
];
