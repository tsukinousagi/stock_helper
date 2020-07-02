<?php
/**
 * 均線方向
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MADFlat()
 * @method static static MADUp()
 * @method static static MADDown()
 */
final class GoodsGraphMADirection extends Enum
{
    const MADFlat = 0; // 持平
    const MADUp   = 1; // 向上
    const MADDown = 2; // 向下
}
