<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TableStatus extends Enum
{
    const available = "available";
    const reserved = "reserved";
    const occupied  = "occupied";
    const unavailable  = "unavailable";

}
