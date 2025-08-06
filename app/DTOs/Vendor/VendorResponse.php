<?php

declare(strict_types=1);

namespace App\DTOs\Vendor;

/**
 * Data transfer object for vendor API responses
 */
class VendorResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly array $data = [],
        public readonly ?string $errorMessage = null,
        public readonly ?string $errorCode = null,
        public readonly array $metadata = [],
        public readonly int $resultCount = 0,
        public readonly ?string $vendorTransactionId = null,
    ) {}

    public static function success(
        array $data = [],
        array $metadata = [],
        int $resultCount = 0,
        ?string $vendorTransactionId = null
    ): self {
        return new self(
            success: true,
            data: $data,
            metadata: $metadata,
            resultCount: $resultCount,
            vendorTransactionId: $vendorTransactionId
        );
    }

    public static function error(
        string $errorMessage,
        ?string $errorCode = null,
        array $metadata = []
    ): self {
        return new self(
            success: false,
            errorMessage: $errorMessage,
            errorCode: $errorCode,
            metadata: $metadata
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error_message' => $this->errorMessage,
            'error_code' => $this->errorCode,
            'metadata' => $this->metadata,
            'result_count' => $this->resultCount,
            'vendor_transaction_id' => $this->vendorTransactionId,
        ];
    }
}