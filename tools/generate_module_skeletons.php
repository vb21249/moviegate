<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$modules = [
    'Common' => [
        'entity' => 'Common',
        'table' => 'api_logs',
        'controller' => 'Health',
        'actions' => ['index'],
        'serviceInterface' => 'CommonServiceInterface',
        'repositoryInterface' => 'CommonRepositoryInterface',
    ],
    'Auth' => [
        'entity' => 'Auth',
        'table' => 'user_sessions',
        'controller' => 'Auth',
        'actions' => ['signup', 'login', 'logout', 'refresh', 'verify-email', 'request-password-reset', 'reset-password'],
        'serviceInterface' => 'AuthServiceInterface',
        'repositoryInterface' => 'AuthRepositoryInterface',
    ],
    'User' => [
        'entity' => 'User',
        'table' => 'users',
        'controller' => 'User',
        'actions' => ['index', 'view', 'activity', 'recent-ratings', 'posts'],
        'serviceInterface' => 'UserServiceInterface',
        'repositoryInterface' => 'UserRepositoryInterface',
    ],
    'Movie' => [
        'entity' => 'Movie',
        'table' => 'movies',
        'controller' => 'Movie',
        'actions' => ['index', 'view', 'watch', 'search'],
        'serviceInterface' => 'MovieServiceInterface',
        'repositoryInterface' => 'MovieRepositoryInterface',
    ],
    'Playlist' => [
        'entity' => 'Playlist',
        'table' => 'playlists',
        'controller' => 'Playlist',
        'actions' => ['index', 'view', 'create', 'add-movie', 'remove-movie'],
        'serviceInterface' => 'PlaylistServiceInterface',
        'repositoryInterface' => 'PlaylistRepositoryInterface',
    ],
    'Review' => [
        'entity' => 'Review',
        'table' => 'reviews',
        'controller' => 'Review',
        'actions' => ['index', 'view', 'create'],
        'serviceInterface' => 'ReviewServiceInterface',
        'repositoryInterface' => 'ReviewRepositoryInterface',
    ],
    'Rating' => [
        'entity' => 'Rating',
        'table' => 'ratings',
        'controller' => 'Rating',
        'actions' => ['index', 'create', 'history'],
        'serviceInterface' => 'RatingServiceInterface',
        'repositoryInterface' => 'RatingRepositoryInterface',
    ],
    'Comment' => [
        'entity' => 'Comment',
        'table' => 'comments',
        'controller' => 'Comment',
        'actions' => ['index', 'create', 'reply', 'like'],
        'serviceInterface' => 'CommentServiceInterface',
        'repositoryInterface' => 'CommentRepositoryInterface',
    ],
    'Feed' => [
        'entity' => 'Feed',
        'table' => 'feed_events',
        'controller' => 'Feed',
        'actions' => ['index', 'rebuild'],
        'serviceInterface' => 'FeedServiceInterface',
        'repositoryInterface' => 'FeedRepositoryInterface',
    ],
    'Recommendation' => [
        'entity' => 'Recommendation',
        'table' => 'recommendation_cache',
        'controller' => 'Recommendation',
        'actions' => ['index', 'rebuild'],
        'serviceInterface' => 'RecommendationServiceInterface',
        'repositoryInterface' => 'RecommendationRepositoryInterface',
    ],
    'Integration' => [
        'entity' => 'Integration',
        'table' => 'integration_logs',
        'controller' => 'Integration',
        'actions' => ['index', 'tmdb-sync', 'soap-ping'],
        'serviceInterface' => 'IntegrationServiceInterface',
        'repositoryInterface' => 'IntegrationRepositoryInterface',
    ],
    'Notification' => [
        'entity' => 'Notification',
        'table' => 'notifications',
        'controller' => 'Notification',
        'actions' => ['index', 'mark-read'],
        'serviceInterface' => 'NotificationServiceInterface',
        'repositoryInterface' => 'NotificationRepositoryInterface',
    ],
    'Search' => [
        'entity' => 'Search',
        'table' => 'movies',
        'controller' => 'Search',
        'actions' => ['index'],
        'serviceInterface' => 'SearchServiceModuleInterface',
        'repositoryInterface' => 'SearchRepositoryInterface',
    ],
];

foreach ($modules as $module => $config) {
    createModule($root, $module, $config);
}

echo "Module skeletons generated.\n";

/**
 * @param string $root
 * @param string $module
 * @param array<string, mixed> $config
 */
function createModule(string $root, string $module, array $config): void
{
    $entity = $config['entity'];
    $table = $config['table'];
    $controller = $config['controller'];
    $actions = $config['actions'];
    $serviceInterface = $config['serviceInterface'];
    $repositoryInterface = $config['repositoryInterface'];

    $modulePath = $root . '/src/Modules/' . $module;
    $directories = [
        '',
        '/Controllers/Api/V1',
        '/Services',
        '/Interfaces',
        '/DTO',
        '/Repositories',
        '/Models',
        '/Entities',
        '/Events',
        '/Exceptions',
        '/Validators',
        '/Factories',
        '/Mappers',
        '/Transformers',
        '/Requests',
        '/Responses',
        '/Jobs',
        '/Policies',
        '/Enums',
    ];

    foreach ($directories as $directory) {
        if (!is_dir($modulePath . $directory)) {
            mkdir($modulePath . $directory, 0777, true);
        }
    }

    write($modulePath . '/Module.php', moduleTemplate($module));
    write($modulePath . '/Interfaces/' . $serviceInterface . '.php', serviceInterfaceTemplate($module, $serviceInterface));
    write($modulePath . '/Interfaces/' . $repositoryInterface . '.php', repositoryInterfaceTemplate($module, $repositoryInterface));
    write($modulePath . '/Services/' . $entity . 'Service.php', serviceTemplate($module, $entity, $serviceInterface, $repositoryInterface));
    write($modulePath . '/Repositories/' . $entity . 'Repository.php', repositoryTemplate($module, $entity, $repositoryInterface));
    write($modulePath . '/Models/' . $entity . 'Record.php', modelTemplate($module, $entity, $table));
    write($modulePath . '/Entities/' . $entity . 'Entity.php', entityTemplate($module, $entity));
    write($modulePath . '/Events/' . $entity . 'Event.php', eventTemplate($module, $entity));
    write($modulePath . '/Exceptions/' . $entity . 'Exception.php', exceptionTemplate($module, $entity));
    write($modulePath . '/Validators/' . $entity . 'Validator.php', validatorTemplate($module, $entity));
    write($modulePath . '/Factories/' . $entity . 'Factory.php', factoryTemplate($module, $entity));
    write($modulePath . '/Mappers/' . $entity . 'Mapper.php', mapperTemplate($module, $entity));
    write($modulePath . '/Transformers/' . $entity . 'Transformer.php', transformerTemplate($module, $entity));
    write($modulePath . '/Requests/' . $entity . 'Request.php', requestTemplate($module, $entity));
    write($modulePath . '/Responses/' . $entity . 'Response.php', responseTemplate($module, $entity));
    write($modulePath . '/Jobs/' . $entity . 'Job.php', jobTemplate($module, $entity));
    write($modulePath . '/Policies/' . $entity . 'Policy.php', policyTemplate($module, $entity));
    write($modulePath . '/Enums/' . $entity . 'Status.php', enumTemplate($module, $entity));
    write($modulePath . '/DTO/' . $entity . 'Dto.php', dtoTemplate($module, $entity));
    write($modulePath . '/Controllers/Api/V1/' . $controller . 'Controller.php', controllerTemplate($module, $entity, $controller, $serviceInterface, $actions));
}

/**
 * @param string $path
 * @param string $content
 */
function write(string $path, string $content): void
{
    file_put_contents($path, $content);
}

function moduleTemplate(string $module): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module};

/**
 * {$module} module definition.
 */
final class Module extends \yii\base\Module
{
    public \$controllerNamespace = 'App\\\\Modules\\\\{$module}\\\\Controllers';
}
PHP;
}

function serviceInterfaceTemplate(string $module, string $interface): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Interfaces;

/**
 * {$module} application service contract.
 */
interface {$interface}
{
    /**
     * @param string \$operation
     * @param array<string, mixed> \$payload
     *
     * @return array<string, mixed>
     */
    public function execute(string \$operation, array \$payload = []): array;
}
PHP;
}

function repositoryInterfaceTemplate(string $module, string $interface): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Interfaces;

/**
 * {$module} repository contract.
 */
interface {$interface}
{
    /**
     * @param array<string, mixed> \$criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array \$criteria = []): array;
}
PHP;
}

function serviceTemplate(string $module, string $entity, string $serviceInterface, string $repositoryInterface): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Services;

use App\Common\Services\AbstractService;
use App\Modules\\{$module}\Interfaces\\{$repositoryInterface};
use App\Modules\\{$module}\Interfaces\\{$serviceInterface};

/**
 * {$entity} application service placeholder.
 */
final class {$entity}Service extends AbstractService implements {$serviceInterface}
{
    public function __construct(
        private readonly {$repositoryInterface} \$repository,
    ) {
    }

    public function execute(string \$operation, array \$payload = []): array
    {
        return [
            'module' => '{$module}',
            'operation' => \$operation,
            'payload' => \$payload,
            'items' => \$this->repository->query(\$payload),
        ];
    }
}
PHP;
}

function repositoryTemplate(string $module, string $entity, string $repositoryInterface): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\\{$module}\Interfaces\\{$repositoryInterface};

/**
 * {$entity} repository placeholder.
 */
final class {$entity}Repository extends BaseRepository implements {$repositoryInterface}
{
    public function query(array \$criteria = []): array
    {
        return [];
    }
}
PHP;
}

function modelTemplate(string $module, string $entity, string $table): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for {$table}.
 *
 * @property int|string \$id
 */
final class {$entity}Record extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%{$table}}}';
    }
}
PHP;
}

function entityTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Entities;

/**
 * {$entity} domain entity.
 */
final class {$entity}Entity
{
    /**
     * @param int|string|null \$id
     * @param array<string, mixed> \$attributes
     */
    public function __construct(
        public readonly int|string|null \$id = null,
        public readonly array \$attributes = [],
    ) {
    }
}
PHP;
}

function eventTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Events;

use App\Common\Events\DomainEvent;

/**
 * {$entity} domain event placeholder.
 */
final class {$entity}Event extends DomainEvent
{
    /**
     * @param array<string, mixed> \$payload
     */
    public function __construct(
        public readonly array \$payload = [],
    ) {
        parent::__construct();
    }
}
PHP;
}

function exceptionTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * {$entity} module exception.
 */
final class {$entity}Exception extends ApiException
{
}
PHP;
}

function validatorTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Validators;

use yii\\validators\\Validator;

/**
 * {$entity} validator placeholder.
 */
final class {$entity}Validator extends Validator
{
    protected function validateValue(\$value): ?array
    {
        return null;
    }
}
PHP;
}

function factoryTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Factories;

use App\Modules\\{$module}\Entities\\{$entity}Entity;

/**
 * {$entity} factory placeholder.
 */
final class {$entity}Factory
{
    /**
     * @param array<string, mixed> \$attributes
     */
    public function make(array \$attributes = []): {$entity}Entity
    {
        return new {$entity}Entity(
            id: \$attributes['id'] ?? null,
            attributes: \$attributes,
        );
    }
}
PHP;
}

function mapperTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Mappers;

use App\Modules\\{$module}\DTO\\{$entity}Dto;
use App\Modules\\{$module}\Entities\\{$entity}Entity;

/**
 * {$entity} mapper placeholder.
 */
final class {$entity}Mapper
{
    public function mapToDto({$entity}Entity \$entity): {$entity}Dto
    {
        return new {$entity}Dto(\$entity->attributes);
    }
}
PHP;
}

function transformerTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Transformers;

use App\Modules\\{$module}\Responses\\{$entity}Response;

/**
 * {$entity} transformer placeholder.
 */
final class {$entity}Transformer
{
    /**
     * @param array<string, mixed> \$payload
     */
    public function transform(array \$payload = []): {$entity}Response
    {
        return new {$entity}Response(\$payload);
    }
}
PHP;
}

function requestTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Requests;

use App\Common\Requests\BaseRequest;

/**
 * {$entity} request model placeholder.
 */
final class {$entity}Request extends BaseRequest
{
    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
PHP;
}

function responseTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Responses;

/**
 * {$entity} response DTO.
 */
final class {$entity}Response
{
    /**
     * @param array<string, mixed> \$payload
     */
    public function __construct(
        public readonly array \$payload = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return \$this->payload;
    }
}
PHP;
}

function jobTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * {$entity} queue job placeholder.
 */
final class {$entity}Job extends BaseJob
{
    /**
     * @param array<string, mixed> \$payload
     */
    public function __construct(
        public readonly array \$payload = [],
        array \$config = [],
    ) {
        parent::__construct(\$config);
    }

    public function execute(\$queue): void
    {
        if (\$queue instanceof Queue) {
            return;
        }
    }
}
PHP;
}

function policyTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Policies;

use App\Common\Policies\BasePolicy;

/**
 * {$entity} authorization policy placeholder.
 */
final class {$entity}Policy extends BasePolicy
{
}
PHP;
}

function enumTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Enums;

/**
 * {$entity} status enum.
 */
enum {$entity}Status: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
PHP;
}

function dtoTemplate(string $module, string $entity): string
{
    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\DTO;

/**
 * {$entity} DTO.
 */
final class {$entity}Dto
{
    /**
     * @param array<string, mixed> \$payload
     */
    public function __construct(
        public readonly array \$payload = [],
    ) {
    }
}
PHP;
}

function controllerTemplate(string $module, string $entity, string $controller, string $serviceInterface, array $actions): string
{
    $methods = [];

    foreach ($actions as $action) {
        $actionMethod = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        $methods[] = <<<PHP
    /**
     * @return array<string, mixed>
     */
    public function action{$actionMethod}(): array
    {
        return \$this->success(
            \$this->service->execute('{$action}', [
                'route' => '{$module}/{$action}',
                'query' => \Yii::\$app->request->queryParams,
                'body' => \Yii::\$app->request->bodyParams,
            ])
        );
    }
PHP;
    }

    $methodsBlock = implode("\n\n", $methods);

    return <<<PHP
<?php

declare(strict_types=1);

namespace App\Modules\\{$module}\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\\{$module}\Interfaces\\{$serviceInterface};

/**
 * @OA\Tag(
 *     name="{$module}",
 *     description="{$module} endpoints"
 * )
 */
final class {$controller}Controller extends BaseApiController
{
    public function __construct(
        string \$id,
        \$module,
        ApiResponseTransformer \$transformer,
        private readonly {$serviceInterface} \$service,
        array \$config = [],
    ) {
        parent::__construct(\$id, \$module, \$transformer, \$config);
    }

{$methodsBlock}
}
PHP;
}
