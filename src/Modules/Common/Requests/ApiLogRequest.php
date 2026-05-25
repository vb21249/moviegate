<?php

declare(strict_types=1);

namespace App\Modules\Common\Requests;

use App\Common\Requests\BaseRequest;

/**
 * API log query request with bounded pagination and filters.
 */
final class ApiLogRequest extends BaseRequest
{
    public int|string $limit = 20;
    public int|string $offset = 0;
    public ?string $correlation_id = null;
    public ?string $request_method = null;
    public int|string|null $response_status = null;
    public ?string $created_from = null;
    public ?string $created_to = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['limit', 'offset'], 'integer'],
            ['limit', 'integer', 'min' => 1, 'max' => 100],
            ['offset', 'integer', 'min' => 0],
            ['correlation_id', 'trim'],
            ['correlation_id', 'string', 'min' => 1, 'max' => 64],
            ['request_method', 'filter', 'filter' => static fn ($value): ?string => $value !== null ? strtoupper(trim((string) $value)) : null],
            ['request_method', 'in', 'range' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']],
            ['response_status', 'integer', 'min' => 100, 'max' => 599],
            [['created_from', 'created_to'], 'trim'],
            [['created_from', 'created_to'], 'string', 'max' => 32],
            [['created_from', 'created_to'], 'validateDateTime'],
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

    public function correlationId(): ?string
    {
        return $this->filledString($this->correlation_id);
    }

    public function requestMethod(): ?string
    {
        return $this->filledString($this->request_method);
    }

    public function responseStatus(): ?int
    {
        return $this->response_status !== null && $this->response_status !== '' ? (int) $this->response_status : null;
    }

    public function createdFrom(): ?string
    {
        return $this->normalizedDateTime($this->created_from);
    }

    public function createdTo(): ?string
    {
        return $this->normalizedDateTime($this->created_to);
    }

    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }

    public function validateDateTime(string $attribute, mixed $params = null): void
    {
        $value = $this->{$attribute};

        if ($value === null || $value === '') {
            return;
        }

        if (strtotime((string) $value) === false) {
            $this->addError($attribute, sprintf('%s must be a valid date or datetime.', $attribute));
        }
    }

    private function filledString(?string $value): ?string
    {
        return $value !== null && $value !== '' ? $value : null;
    }

    private function normalizedDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return date('Y-m-d H:i:s', (int) strtotime($value));
    }
}
