<?php
namespace Izumi\Backend\app\Models\Product\Errors;
use Izumi\Backend\app\Shared\Error;

final class InvalidQuantityError extends Error
{
    public function __construct()
    {
        parent::__construct(
            'InvalidQuantity',
            'Quantity cannot be negative',
            3
        );
    }
}
?>
