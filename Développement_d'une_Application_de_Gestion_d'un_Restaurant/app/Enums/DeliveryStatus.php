<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class DeliveryStatus extends Enum
{
    const cancelled = "cancelled";
    const delivered = "delivered";
    const transit = "transit";
    const pending="pending";
}
