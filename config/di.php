<?php

declare(strict_types=1);

use App\Common\Contracts\ApiLogWriterInterface;
use App\Common\Contracts\CacheInterface;
use App\Common\Contracts\CircuitBreakerInterface;
use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Contracts\FeatureFlagInterface;
use App\Common\Contracts\HttpClientInterface;
use App\Common\Contracts\JwtServiceInterface;
use App\Common\Contracts\RateLimiterInterface;
use App\Common\Contracts\RequestResponseLoggerInterface;
use App\Common\Contracts\SearchServiceInterface;
use App\Common\Contracts\SoapClientInterface;
use App\Common\Events\DomainEventBusInterface;
use App\Common\Events\DomainEventDispatcherInterface;
use App\Common\Events\DomainEventSubscriberRegistry;
use App\Common\Events\QueuedDomainEventBus;
use App\Common\Events\SyncDomainEventDispatcher;
use App\Common\Http\GuzzleHttpClient;
use App\Common\Http\SimpleCircuitBreaker;
use App\Common\Logging\CorrelationIdProvider;
use App\Common\Logging\DbApiLogWriter;
use App\Common\Logging\RequestResponseLogger;
use App\Common\Services\ArrayFeatureFlagService;
use App\Common\Services\FirebaseJwtService;
use App\Common\Services\RedisRateLimiter;
use App\Common\Services\YiiCacheAdapter;
use App\Common\Soap\NativeSoapClientAdapter;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use App\Modules\Common\Interfaces\CommonServiceInterface;
use App\Modules\Common\Repositories\CommonRepository;
use App\Modules\Common\Services\CommonService;
use App\Modules\Integration\Clients\LegacySoapGateway;
use App\Modules\Integration\Clients\LegacySoapGatewayInterface;
use App\Modules\Integration\Clients\TmdbClient;
use App\Modules\Integration\Clients\TmdbClientInterface;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Interfaces\AuthServiceInterface;
use App\Modules\Auth\Repositories\AuthRepository;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;
use App\Modules\Comment\Interfaces\CommentServiceInterface;
use App\Modules\Comment\Mappers\CommentMapper;
use App\Modules\Comment\Repositories\CommentRepository;
use App\Modules\Comment\Services\CommentService;
use App\Modules\Comment\Transformers\CommentTransformer;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use App\Modules\Feed\Interfaces\FeedServiceInterface;
use App\Modules\Feed\Repositories\FeedRepository;
use App\Modules\Feed\Services\FeedService;
use App\Modules\Feed\Subscribers\SocialActivityFeedSubscriber;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;
use App\Modules\Integration\Repositories\IntegrationRepository;
use App\Modules\Integration\Services\IntegrationService;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use App\Modules\Movie\Interfaces\MovieServiceInterface;
use App\Modules\Movie\Repositories\MovieRepository;
use App\Modules\Movie\Services\MovieService;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;
use App\Modules\Notification\Repositories\NotificationRepository;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Subscribers\SocialActivityNotificationSubscriber;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;
use App\Modules\Playlist\Interfaces\PlaylistServiceInterface;
use App\Modules\Playlist\Repositories\PlaylistRepository;
use App\Modules\Playlist\Services\PlaylistService;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;
use App\Modules\Rating\Interfaces\RatingServiceInterface;
use App\Modules\Rating\Mappers\RatingMapper;
use App\Modules\Rating\Repositories\RatingRepository;
use App\Modules\Rating\Services\RatingService;
use App\Modules\Rating\Transformers\RatingTransformer;
use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;
use App\Modules\Recommendation\Interfaces\RecommendationServiceInterface;
use App\Modules\Recommendation\Repositories\RecommendationRepository;
use App\Modules\Recommendation\Services\RecommendationService;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;
use App\Modules\Review\Interfaces\ReviewServiceInterface;
use App\Modules\Review\Mappers\ReviewMapper;
use App\Modules\Review\Repositories\ReviewRepository;
use App\Modules\Review\Services\ReviewService;
use App\Modules\Review\Transformers\ReviewTransformer;
use App\Modules\Search\Interfaces\SearchRepositoryInterface;
use App\Modules\Search\Interfaces\SearchServiceModuleInterface;
use App\Modules\Search\Repositories\SearchRepository;
use App\Modules\Search\Services\SearchService;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Interfaces\UserServiceInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Services\UserService;
use yii\caching\CacheInterface as YiiCacheInterface;

return [
    YiiCacheInterface::class => static fn (): YiiCacheInterface => \Yii::$app->cache,
    CorrelationIdProviderInterface::class => CorrelationIdProvider::class,
    RequestResponseLoggerInterface::class => RequestResponseLogger::class,
    HttpClientInterface::class => static fn (): HttpClientInterface => new GuzzleHttpClient(
        new \GuzzleHttp\Client()
    ),
    SoapClientInterface::class => NativeSoapClientAdapter::class,
    CacheInterface::class => YiiCacheAdapter::class,
    RateLimiterInterface::class => RedisRateLimiter::class,
    FeatureFlagInterface::class => ArrayFeatureFlagService::class,
    JwtServiceInterface::class => FirebaseJwtService::class,
    CircuitBreakerInterface::class => SimpleCircuitBreaker::class,
    ApiLogWriterInterface::class => DbApiLogWriter::class,
    DomainEventBusInterface::class => QueuedDomainEventBus::class,
    DomainEventDispatcherInterface::class => SyncDomainEventDispatcher::class,
    DomainEventSubscriberRegistry::class => static fn (): DomainEventSubscriberRegistry => new DomainEventSubscriberRegistry([
        \Yii::$container->get(SocialActivityFeedSubscriber::class),
        \Yii::$container->get(SocialActivityNotificationSubscriber::class),
    ]),
    SearchServiceInterface::class => SearchService::class,
    SearchServiceModuleInterface::class => SearchService::class,
    CommonServiceInterface::class => CommonService::class,
    CommonRepositoryInterface::class => CommonRepository::class,
    AuthServiceInterface::class => AuthService::class,
    AuthRepositoryInterface::class => AuthRepository::class,
    UserServiceInterface::class => UserService::class,
    UserRepositoryInterface::class => UserRepository::class,
    MovieServiceInterface::class => MovieService::class,
    MovieRepositoryInterface::class => MovieRepository::class,
    PlaylistServiceInterface::class => PlaylistService::class,
    PlaylistRepositoryInterface::class => PlaylistRepository::class,
    ReviewServiceInterface::class => static fn (): ReviewServiceInterface => new ReviewService(
        \Yii::$container->get(ReviewRepositoryInterface::class),
        \Yii::$container->get(ReviewMapper::class),
        \Yii::$container->get(ReviewTransformer::class),
        \Yii::$container->get(DomainEventBusInterface::class),
    ),
    ReviewRepositoryInterface::class => ReviewRepository::class,
    RatingServiceInterface::class => static fn (): RatingServiceInterface => new RatingService(
        \Yii::$container->get(RatingRepositoryInterface::class),
        \Yii::$container->get(RatingMapper::class),
        \Yii::$container->get(RatingTransformer::class),
        \Yii::$container->get(DomainEventBusInterface::class),
    ),
    RatingRepositoryInterface::class => RatingRepository::class,
    CommentServiceInterface::class => static fn (): CommentServiceInterface => new CommentService(
        \Yii::$container->get(CommentRepositoryInterface::class),
        \Yii::$container->get(CommentMapper::class),
        \Yii::$container->get(CommentTransformer::class),
        \Yii::$container->get(DomainEventBusInterface::class),
    ),
    CommentRepositoryInterface::class => CommentRepository::class,
    FeedServiceInterface::class => FeedService::class,
    FeedRepositoryInterface::class => FeedRepository::class,
    RecommendationServiceInterface::class => RecommendationService::class,
    RecommendationRepositoryInterface::class => RecommendationRepository::class,
    IntegrationServiceInterface::class => IntegrationService::class,
    IntegrationRepositoryInterface::class => IntegrationRepository::class,
    NotificationServiceInterface::class => NotificationService::class,
    NotificationRepositoryInterface::class => NotificationRepository::class,
    SearchRepositoryInterface::class => SearchRepository::class,
    TmdbClientInterface::class => TmdbClient::class,
    LegacySoapGatewayInterface::class => LegacySoapGateway::class,
];
