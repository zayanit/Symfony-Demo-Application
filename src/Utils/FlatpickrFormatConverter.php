<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utils;

/**
 * This class is used to convert PHP date format to flatpickr format.
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class FlatpickrFormatConverter
{
    /**
     * This defines the mapping between PHP ICU date format (key) and flatpickr date format (value)
     * For ICU formats see http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
     * For flatpickr formats see https://flatpickr.js.org/formatting/.
     *
     * @var array
     */
    private static $formatConvertRules = [
        // year
        'yyyy' => 'Y', 'yy' => 'y', 'y' => 'Y',
        // month
        'MM' => 'm',
        // day
        'dd' => 'd', 'd' => 'j',
        // hour, minute, second
        'HH' => 'H', 'mm' => 'i', 'ss' => 'S',
        // letter 'T'
        '\'T\'' => 'T',
    ];

    /**
     * Returns associated flatpickr format.
     */
    public function convert(string $format): string
    {
        return strtr($format, self::$formatConvertRules);
    }
}
