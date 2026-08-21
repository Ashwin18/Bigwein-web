<?php

namespace App\Http\Requests\Api\Mobile;

class UpdateOwnerPropertyRequest extends OwnerPropertyRequest
{
    protected function titleImageRule(): string
    {
        return 'nullable';
    }
}
