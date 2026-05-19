<?php

declare(strict_types=1);

namespace App\Modules\Movie\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Movie catalog query request with bounded offset pagination.
 */
final class MovieRequest extends BaseRequest
{
    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $q = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer'],
            ['limit', 'integer', 'min' => 1, 'max' => 100],
            ['offset', 'integer', 'min' => 0],
            ['q', 'string', 'min' => 1, 'max' => 190],
            ['q', 'trim'],
        ];
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
     * @return string
     */
    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
