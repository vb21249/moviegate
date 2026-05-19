<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Requests;

use App\Common\Requests\BaseRequest;
use App\Modules\Playlist\Enums\PlaylistVisibility;

/**
 * Playlist request model with bounded pagination and mutation validation.
 */
final class PlaylistRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $q = null;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $description = null;
    public ?string $visibility = null;
    public bool|int|string|null $is_default_watch_later = null;

    /**
     * @var array<string, mixed>
     */
    private array $input = [];

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['limit', 'integer', 'min' => 1, 'max' => 100, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['offset', 'integer', 'min' => 0, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['q', 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['q', 'string', 'min' => 1, 'max' => 190, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['name', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['name', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['name', 'string', 'min' => 1, 'max' => 255, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['slug', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['slug', 'string', 'min' => 1, 'max' => 190, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [
                'slug',
                'match',
                'pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'message' => 'Slug may contain lowercase Latin letters, numbers and hyphens.',
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],
            ['description', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['description', 'string', 'max' => 5000, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [
                'visibility',
                'in',
                'range' => PlaylistVisibility::values(),
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX, self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],
            [
                'is_default_watch_later',
                'boolean',
                'trueValue' => true,
                'falseValue' => false,
                'strict' => false,
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'q', 'visibility'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'q', 'visibility'],
            self::SCENARIO_CREATE => ['name', 'slug', 'description', 'visibility', 'is_default_watch_later'],
            self::SCENARIO_UPDATE => ['name', 'slug', 'description', 'visibility', 'is_default_watch_later'],
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    public function loadFromArray(array $input): void
    {
        $this->input = $input;
        $this->load($input, '');
    }

    /**
     * @return int
     */
    public function limit(): int
    {
        return (int) $this->limit;
    }

    /**
     * @return int
     */
    public function offset(): int
    {
        return (int) $this->offset;
    }

    /**
     * @return string|null
     */
    public function query(): ?string
    {
        return $this->q !== null && $this->q !== '' ? $this->q : null;
    }

    /**
     * @return string|null
     */
    public function requestedVisibility(): ?string
    {
        return $this->visibility !== null && $this->visibility !== '' ? $this->visibility : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function playlistAttributes(): array
    {
        $attributes = [];

        foreach (['name', 'slug', 'description', 'visibility', 'is_default_watch_later'] as $attribute) {
            if (!array_key_exists($attribute, $this->input)) {
                continue;
            }

            $value = $this->{$attribute};

            if ($attribute === 'is_default_watch_later') {
                $value = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
            }

            $attributes[$attribute] = $value;
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
