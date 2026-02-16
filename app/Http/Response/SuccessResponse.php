<?php

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response;

class SuccessResponse implements IResponseArrayFormat
{

    private int $code = Response::HTTP_OK;

    private array|string|null $data;

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getData(): array|string|null
    {
        return $this->data;
    }

    public function setData(array|string|null $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getArrayFormat(): array
    {
        return [
            'success' => true,
            'code' => $this->code,
            'content' => $this->data
        ];
    }

    public function getCode(): int {
        return $this->code;
    }
}
