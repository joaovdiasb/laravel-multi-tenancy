<?php

namespace Joaovdiasb\LaravelMultiTenancy\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TenancyNotFound extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => 'Empresa não encontrada.'], 404);
    }
}
