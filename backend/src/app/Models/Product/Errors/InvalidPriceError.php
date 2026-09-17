<?php
namespace Izumi\Backend\app\Models\Product\Errors;

use Izumi\Backend\app\Shared\Error;

final class InvalidPriceError extends Error
{
    public function __construct()
    {
        parent::__construct(
            'InvalidPrice',
            'Price cannot be negative',
            2
        );
    }
}
?>
