<?php
/**
 * 各種價量
 */

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Reserved()
 * @method static static PriceOpen()
 * @method static static PriceHigh()
 * @method static static PriceLow()
 * @method static static PriceClose()
 * @method static static Volume()
 * @method static static Price5MA()
 * @method static static Price10MA()
 * @method static static Price20MA()
 * @method static static Price5MADirection()
 * @method static static Price10MADirection()
 * @method static static Price20MADirection()
 */
final class GoodsGraphDataType extends Enum
{

    const Reserved           = 0; // 保留
    const PriceOpen          = 1; // 開盤價
    const PriceHigh          = 2; // 最高價
    const PriceLow           = 3; // 最低價
    const PriceClose         = 4; // 收盤價
    const Volume             = 5; // 成交量
    const Price5MA           = 6; // 5日均價
    const Price10MA          = 7; // 10日均價
    const Price20MA          = 8; // 20日均價
    const Price5MADirection  = 9; // 5日均線方向
    const Price10MADirection = 10; // 10日均線方向
    const Price20MADirection = 11; // 20日均線方向

}
