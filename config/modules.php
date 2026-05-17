<?php

declare(strict_types=1);

return [
    'common' => App\Modules\Common\Module::class,
    'auth' => App\Modules\Auth\Module::class,
    'user' => App\Modules\User\Module::class,
    'movie' => App\Modules\Movie\Module::class,
    'playlist' => App\Modules\Playlist\Module::class,
    'review' => App\Modules\Review\Module::class,
    'rating' => App\Modules\Rating\Module::class,
    'comment' => App\Modules\Comment\Module::class,
    'feed' => App\Modules\Feed\Module::class,
    'recommendation' => App\Modules\Recommendation\Module::class,
    'integration' => App\Modules\Integration\Module::class,
    'notification' => App\Modules\Notification\Module::class,
    'search' => App\Modules\Search\Module::class,
];
