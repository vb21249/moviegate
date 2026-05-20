<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Recommendation request with bounded pagination and cache controls.
 */
final class RecommendationRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_REBUILD = 'rebuild';

    public const SOURCE_PERSONALIZED = 'personalized';
    public const SOURCE_POPULAR = 'popular';
    public const SOURCE_RECENT = 'recent';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $source = null;
    public bool|int|string|null $refresh = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['limit', 'integer', 'min' => 1, 'max' => 50, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['offset', 'integer', 'min' => 0, 'max' => 200, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['source', 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX, self::SCENARIO_REBUILD]],
            [
                'source',
                'in',
                'range' => [self::SOURCE_PERSONALIZED, self::SOURCE_POPULAR, self::SOURCE_RECENT],
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX, self::SCENARIO_REBUILD],
            ],
            ['refresh', 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'source', 'refresh'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'source', 'refresh'],
            self::SCENARIO_REBUILD => ['source'],
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    public function loadFromArray(array $input): void
    {
        $this->load($input, '');
    }

    public function limit(): int
    {
        return (int) $this->limit;
    }

    public function offset(): int
    {
        return (int) $this->offset;
    }

    public function source(): ?string
    {
        return $this->source !== null && $this->source !== '' ? $this->source : null;
    }

    public function refresh(): bool
    {
        return filter_var($this->refresh ?? false, FILTER_VALIDATE_BOOL);
    }

    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
