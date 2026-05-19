<?php

declare(strict_types=1);

namespace App\Modules\Feed\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Feed request model with bounded pagination and event filters.
 */
final class FeedRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_MINE = 'mine';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public int|string|null $user_id = null;
    public int|string|null $entity_id = null;
    public ?string $event_type = null;
    public ?string $entity_type = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer', 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MINE,
            ]],
            ['limit', 'integer', 'min' => 1, 'max' => 100, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MINE,
            ]],
            ['offset', 'integer', 'min' => 0, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MINE,
            ]],
            [['user_id', 'entity_id'], 'integer', 'min' => 1, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
            ]],
            ['entity_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_MINE]],
            [['event_type', 'entity_type'], 'trim', 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MINE,
            ]],
            [['event_type', 'entity_type'], 'string', 'min' => 1, 'max' => 100, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MINE,
            ]],
            [
                ['event_type', 'entity_type'],
                'match',
                'pattern' => '/^[a-z][a-z0-9_]*$/',
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX, self::SCENARIO_MINE],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'user_id', 'event_type', 'entity_type', 'entity_id'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'user_id', 'event_type', 'entity_type', 'entity_id'],
            self::SCENARIO_MINE => ['limit', 'offset', 'event_type', 'entity_type', 'entity_id'],
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

    public function userId(): ?int
    {
        return $this->user_id !== null && $this->user_id !== '' ? (int) $this->user_id : null;
    }

    public function entityId(): ?int
    {
        return $this->entity_id !== null && $this->entity_id !== '' ? (int) $this->entity_id : null;
    }

    public function eventType(): ?string
    {
        return $this->event_type !== null && $this->event_type !== '' ? $this->event_type : null;
    }

    public function entityType(): ?string
    {
        return $this->entity_type !== null && $this->entity_type !== '' ? $this->entity_type : null;
    }

    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
