<?php

declare(strict_types=1);

namespace App\Modules\Integration\Requests;

use App\Common\Requests\BaseRequest;

/**
 * TMDB movie sync request model.
 */
final class IntegrationRequest extends BaseRequest
{
    public int|string|null $tmdb_id = null;
    public ?string $query = null;
    public string $language = 'en-US';
    public bool|int|string $include_adult = false;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['tmdb_id', 'query'], 'default', 'value' => null],
            ['language', 'default', 'value' => 'en-US'],
            ['include_adult', 'default', 'value' => false],
            ['tmdb_id', 'integer', 'min' => 1],
            ['query', 'trim'],
            ['query', 'string', 'min' => 1, 'max' => 190],
            ['language', 'trim'],
            ['language', 'match', 'pattern' => '/^[a-z]{2}-[A-Z]{2}$/'],
            ['include_adult', 'boolean'],
            ['tmdb_id', 'validateSource'],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateSource(string $attribute, mixed $params = null): void
    {
        if ($this->tmdbId() === null && $this->query() === null) {
            $this->addError($attribute, 'Either tmdb_id or query is required.');
        }
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
