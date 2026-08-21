<?php

namespace App\Http\Requests\Api\Mobile;

class StoreOwnerPropertyRequest extends OwnerPropertyRequest
{
    protected function titleImageRule(): string
    {
        return 'required';
    }
}
