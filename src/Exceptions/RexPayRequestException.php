<?php

namespace SotersFolio\RexPay\Exceptions;

use Exception;

class RexPayRequestException extends Exception
{
    protected int $httpCode;
    protected array $responseBody;

    public function __construct(string $message = "", int $httpCode = 0, array $responseBody = [], ?Exception $previous = null)
    {
        parent::__construct($message, $httpCode, $previous);
        $this->httpCode = $httpCode;
        $this->responseBody = $responseBody;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}