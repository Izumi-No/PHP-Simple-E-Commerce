<?php

namespace Izumi\Backend\app\Models\Auth\Errors;

use Izumi\Backend\app\Shared\Errors\Error;

final readonly class MissingProviderSubjectError extends Error
{
    public function __construct()
    {
        parent::__construct(
            code: 'MissingProviderSubject',
            message: 'Provider subject is required for OpenID Connect identities',
        );
    }
}
