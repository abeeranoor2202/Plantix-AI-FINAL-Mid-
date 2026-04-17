<?php

namespace App\Http\Response;

final class ApiErrorCode
{
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const FORBIDDEN = 'FORBIDDEN';
    public const RATE_LIMITED = 'RATE_LIMITED';
    public const STATE_CONFLICT = 'STATE_CONFLICT';
    public const BUSINESS_RULE_VIOLATION = 'BUSINESS_RULE_VIOLATION';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
}
