<?php

declare(strict_types=1);

namespace App\Modules\Rating\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Rating request model with pagination and mutation validation.
 */
final class RatingRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';
    public const SCENARIO_HISTORY = 'history';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public int|string|null $movie_id = null;
    public int|string|null $user_id = null;
    public int|string|null $score = null;
    public ?string $review_text = null;

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
            [['limit', 'offset'], 'integer', 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_HISTORY,
            ]],
            ['limit', 'integer', 'min' => 1, 'max' => 100, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_HISTORY,
            ]],
            ['offset', 'integer', 'min' => 0, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
                self::SCENARIO_HISTORY,
            ]],
            [['movie_id', 'user_id'], 'integer', 'min' => 1, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
            ]],
            ['movie_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_HISTORY]],
            ['score', 'integer', 'min' => 1, 'max' => 10, 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_INDEX,
            ]],

            ['movie_id', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['movie_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_CREATE]],
            ['score', 'required', 'on' => [self::SCENARIO_CREATE]],
            ['score', 'integer', 'min' => 1, 'max' => 10, 'on' => [
                self::SCENARIO_CREATE,
                self::SCENARIO_UPDATE,
            ]],
            ['review_text', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['review_text', 'string', 'max' => 5000, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'movie_id', 'user_id', 'score'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'movie_id', 'user_id', 'score'],
            self::SCENARIO_CREATE => ['movie_id', 'score', 'review_text'],
            self::SCENARIO_UPDATE => ['score', 'review_text'],
            self::SCENARIO_HISTORY => ['limit', 'offset', 'movie_id'],
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

    public function movieId(): ?int
    {
        return $this->movie_id !== null && $this->movie_id !== '' ? (int) $this->movie_id : null;
    }

    public function userId(): ?int
    {
        return $this->user_id !== null && $this->user_id !== '' ? (int) $this->user_id : null;
    }

    public function score(): ?int
    {
        return $this->score !== null && $this->score !== '' ? (int) $this->score : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function ratingAttributes(): array
    {
        $attributes = [];

        foreach (['movie_id', 'score', 'review_text'] as $attribute) {
            if (!array_key_exists($attribute, $this->input)) {
                continue;
            }

            $value = $this->{$attribute};

            if (in_array($attribute, ['movie_id', 'score'], true)) {
                $value = $value !== null && $value !== '' ? (int) $value : null;
            }

            if ($attribute === 'review_text' && $value === '') {
                $value = null;
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
