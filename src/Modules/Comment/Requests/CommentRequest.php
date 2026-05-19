<?php

declare(strict_types=1);

namespace App\Modules\Comment\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Comment request model with pagination and mutation validation.
 */
final class CommentRequest extends BaseRequest
{
    public const SCENARIO_INDEX = 'index';
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_REPLY = 'reply';
    public const SCENARIO_UPDATE = 'update';

    public int|string $limit = 20;
    public int|string $offset = 0;
    public int|string|null $movie_id = null;
    public int|string|null $review_id = null;
    public int|string|null $user_id = null;
    public int|string|null $parent_id = null;
    public ?string $body = null;

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
            [
                ['movie_id', 'review_id', 'user_id', 'parent_id'],
                'integer',
                'min' => 1,
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_INDEX],
            ],

            [
                ['movie_id', 'review_id'],
                'integer',
                'min' => 1,
                'on' => [self::SCENARIO_CREATE],
            ],
            ['body', 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_REPLY, self::SCENARIO_UPDATE]],
            ['body', 'trim', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_REPLY, self::SCENARIO_UPDATE]],
            [
                'body',
                'string',
                'min' => 1,
                'max' => 5000,
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_REPLY, self::SCENARIO_UPDATE],
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['limit', 'offset', 'movie_id', 'review_id', 'user_id', 'parent_id'],
            self::SCENARIO_INDEX => ['limit', 'offset', 'movie_id', 'review_id', 'user_id', 'parent_id'],
            self::SCENARIO_CREATE => ['movie_id', 'review_id', 'body'],
            self::SCENARIO_REPLY => ['body'],
            self::SCENARIO_UPDATE => ['body'],
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

    public function reviewId(): ?int
    {
        return $this->review_id !== null && $this->review_id !== '' ? (int) $this->review_id : null;
    }

    public function userId(): ?int
    {
        return $this->user_id !== null && $this->user_id !== '' ? (int) $this->user_id : null;
    }

    public function parentId(): ?int
    {
        return $this->parent_id !== null && $this->parent_id !== '' ? (int) $this->parent_id : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function commentAttributes(): array
    {
        $attributes = [];

        foreach (['movie_id', 'review_id', 'parent_id', 'body'] as $attribute) {
            if (!array_key_exists($attribute, $this->input)) {
                continue;
            }

            $value = $this->{$attribute};

            if (in_array($attribute, ['movie_id', 'review_id', 'parent_id'], true)) {
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
