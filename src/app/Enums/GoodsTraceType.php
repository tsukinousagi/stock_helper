<?php
/**
 * 要追蹤和通知的價量變化
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

final class GoodsTraceType extends Enum
{
    const Turning = 1; // 股價轉折
}
