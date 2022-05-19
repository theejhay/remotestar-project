<?php

namespace MercuryHolidays\Search;

class Constants
{
    public const AVAILABLE = 'available';
    public const GROUND_FLOOR = 'ground_floor';
    public const NUMBER = 'number';
    public const PRICE = 'price';
    public const HOTEL = 'hotel';

    public static function constants(): array
    {
        return [
            self::AVAILABLE,
            self::GROUND_FLOOR,
            self::HOTEL,
            self::NUMBER,
            self::PRICE,
        ];
    }
}
