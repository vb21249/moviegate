<?php

declare(strict_types=1);

namespace App\Modules\Integration\Requests;

use App\Common\Requests\BaseRequest;

/**
 * External movie sync request model.
 */
final class IntegrationRequest extends BaseRequest
{
    public const SCENARIO_TMDB_SYNC = 'tmdb-sync';
    public const SCENARIO_OMDB_SYNC = 'omdb-sync';

    public int|string|null $tmdb_id = null;
    public ?string $imdb_id = null;
    public ?string $query = null;
    public string $language = 'en-US';
    public bool|int|string $include_adult = false;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['tmdb_id', 'imdb_id', 'query'], 'default', 'value' => null],
            ['language', 'default', 'value' => 'en-US'],
            ['include_adult', 'default', 'value' => false],
            ['tmdb_id', 'integer', 'min' => 1, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_TMDB_SYNC]],
            ['imdb_id', 'trim', 'on' => [self::SCENARIO_OMDB_SYNC]],
            ['imdb_id', 'match', 'pattern' => '/^tt\d{7,10}$/i', 'on' => [self::SCENARIO_OMDB_SYNC]],
            ['query', 'trim'],
            ['query', 'string', 'min' => 1, 'max' => 190],
            ['language', 'trim'],
            ['language', 'match', 'pattern' => '/^[a-z]{2}-[A-Z]{2}$/', 'on' => [
                self::SCENARIO_DEFAULT,
                self::SCENARIO_TMDB_SYNC,
            ]],
            ['include_adult', 'boolean', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_TMDB_SYNC]],
            [
                'tmdb_id',
                'validateTmdbSource',
                'skipOnEmpty' => false,
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_TMDB_SYNC],
            ],
            [
                'imdb_id',
                'validateOmdbSource',
                'skipOnEmpty' => false,
                'on' => [self::SCENARIO_OMDB_SYNC],
            ],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateTmdbSource(string $attribute, mixed $params = null): void
    {
        if ($this->tmdbId() === null && $this->query() === null) {
            $this->addError($attribute, 'Either tmdb_id or query is required.');
        }
    }

    /**
     * @param string $attribute
     */
    public function validateOmdbSource(string $attribute, mixed $params = null): void
    {
        if ($this->imdbId() === null && $this->query() === null) {
            $this->addError($attribute, 'Either imdb_id or query is required.');
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['tmdb_id', 'query', 'language', 'include_adult'],
            self::SCENARIO_TMDB_SYNC => ['tmdb_id', 'query', 'language', 'include_adult'],
            self::SCENARIO_OMDB_SYNC => ['imdb_id', 'query'],
        ];
    }

    /**
     * @return int|null
     */
    public function tmdbId(): ?int
    {
        return $this->tmdb_id !== null && $this->tmdb_id !== '' ? (int) $this->tmdb_id : null;
    }

    /**
     * @return string|null
     */
    public function imdbId(): ?string
    {
        $imdbId = $this->imdb_id !== null ? strtolower(trim($this->imdb_id)) : '';

        return $imdbId !== '' ? $imdbId : null;
    }

    /**
     * @return string|null
     */
    public function query(): ?string
    {
        $query = $this->query !== null ? trim($this->query) : '';

        return $query !== '' ? $query : null;
    }

    /**
     * @return string
     */
    public function language(): string
    {
        return $this->language;
    }

    /**
     * @return bool
     */
    public function includeAdult(): bool
    {
        return filter_var($this->include_adult, FILTER_VALIDATE_BOOL);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'tmdb_id' => $this->tmdbId(),
            'imdb_id' => $this->imdbId(),
            'query' => $this->query(),
            'language' => $this->language(),
            'include_adult' => $this->includeAdult(),
        ];
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
