<?php

declare(strict_types=1);

namespace App\Modules\User\Requests;

use App\Common\Requests\BaseRequest;

/**
 * User list request with bounded offset pagination.
 */
final class UserRequest extends BaseRequest
{
    public int|string $limit = 20;
    public int|string $offset = 0;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer'],
            ['limit', 'integer', 'min' => 1, 'max' => 100],
            ['offset', 'integer', 'min' => 0],
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
     * @return string
     */
    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
