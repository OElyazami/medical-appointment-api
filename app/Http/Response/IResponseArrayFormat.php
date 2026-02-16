<?php

namespace App\Http\Response;

interface IResponseArrayFormat{
    public function getArrayFormat(): array;

    public function getCode(): int;
}