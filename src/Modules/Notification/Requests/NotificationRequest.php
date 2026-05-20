<?php

declare(strict_types=1);

namespace App\Modules\Notification\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Notification request model with bounded pagination and filters.
 */
final class NotificationRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_MARK_ALL_READ = 'mark-all-read';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $type = null;
    public bool|int|string|null $unread_only = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['limit', 'integer', 'min' => 1, 'max' => 100, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['offset', 'integer', 'min' => 0, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['unread_only', 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            [['type'], 'trim', 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MARK_ALL_READ,
            ]],
            [['type'], 'string', 'min' => 1, 'max' => 100, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_MARK_ALL_READ,
            ]],
            [
                ['type'],
                'match',
                'pattern' => '/^[a-z][a-z0-9_]*$/',
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX, self::SCENARIO_MARK_ALL_READ],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'type', 'unread_only'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'type', 'unread_only'],
            self::SCENARIO_MARK_ALL_READ => ['type'],
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

    public function type(): ?string
    {
        return $this->type !== null && $this->type !== '' ? $this->type : null;
    }

    public function unreadOnly(): bool
    {
        return filter_var($this->unread_only ?? false, FILTER_VALIDATE_BOOL);
    }

    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
