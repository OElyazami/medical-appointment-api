<?php

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response;

class ErrorResponse implements IResponseArrayFormat
{

    private int $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    private string $message;

    private array $errors = [];

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function getArrayFormat(): array
    {
        $response = [
            'code' => $this->code
        ];

        if ($this->message) {
            $response['messages'] = $this->message;
        }
        if ($this->errors) {
            $response['errors'] = $this->errors;
        }

        return [
            'success' => false,
            'code' => $this->code,
            'content' => $response
        ];
    }

    public function getCode(): int {
        return $this->code;
    }
}
