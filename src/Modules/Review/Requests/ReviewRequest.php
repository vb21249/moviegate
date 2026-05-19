<?php

declare(strict_types=1);

namespace App\Modules\Review\Requests;

use App\Common\Requests\BaseRequest;
use App\Modules\Review\Enums\ReviewStatus;

/**
 * Review request model with pagination and mutation validation.
 */
final class ReviewRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $q = null;
    public int|string|null $movie_id = null;
    public int|string|null $user_id = null;
    public int|string|null $rating_id = null;
    public ?string $title = null;
    public ?string $body = null;
    public ?string $status = null;

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
            [['movie_id', 'user_id'], 'integer', 'min' => 1, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['q', 'trim', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],
            ['q', 'string', 'min' => 1, 'max' => 190, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX]],

            ['movie_id', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['movie_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_CREATE]],
            ['rating_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['title', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['title', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['title', 'string', 'min' => 1, 'max' => 255, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['body', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['body', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['body', 'string', 'min' => 10, 'max' => 20000, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [
                'status',
                'in',
                'range' => [ReviewStatus::Draft->value, ReviewStatus::Published->value, ReviewStatus::Archived->value],
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
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'q', 'movie_id', 'user_id'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'q', 'movie_id', 'user_id'],
            self::SCENARIO_CREATE => ['movie_id', 'rating_id', 'title', 'body', 'status'],
            self::SCENARIO_UPDATE => ['rating_id', 'title', 'body', 'status'],
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

    public function limit(): int
    {
        return (int) $this->limit;
    }

    public function offset(): int
    {
        return (int) $this->offset;
    }

    public function query(): ?string
    {
        return $this->q !== null && $this->q !== '' ? $this->q : null;
    }

    public function movieId(): ?int
    {
        return $this->movie_id !== null && $this->movie_id !== '' ? (int) $this->movie_id : null;
    }

    public function requiredMovieId(): int
    {
        return (int) $this->movie_id;
    }

    public function userId(): ?int
    {
        return $this->user_id !== null && $this->user_id !== '' ? (int) $this->user_id : null;
    }

    public function ratingId(): ?int
    {
        return $this->rating_id !== null && $this->rating_id !== '' ? (int) $this->rating_id : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function reviewAttributes(): array
    {
        $attributes = [];

        foreach (['movie_id', 'rating_id', 'title', 'body', 'status'] as $attribute) {
            if (!array_key_exists($attribute, $this->input)) {
                continue;
            }

            $value = $this->{$attribute};

            if (in_array($attribute, ['movie_id', 'rating_id'], true)) {
                $value = $value !== null && $value !== '' ? (int) $value : null;
            }

            $attributes[$attribute] = $value;
        }

        return $attributes;
    }

    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
