<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utils;

use App\Utils\FlatpickrFormatConverter;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the application utils.
 *
 * See https://symfony.com/doc/current/book/testing.html#unit-tests
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class FlatpickrFormatConverterTest extends TestCase
{
    /**
     * @dataProvider getFormats
     */
    public function testConvert(string $icuFormat, string $flatpickrFormat)
    {
        $this->assertSame($flatpickrFormat, (new FlatpickrFormatConverter())->convert($icuFormat));
    }

    public function getFormats()
    {
        // this is the actual format used by DateTimePickerType for the 'publishedAt' field
        yield ['yyyy-MM-dd\'T\'HH:mm:ss', 'Y-m-dTH:i:S'];
        yield ['yyyy-MM-dd', 'Y-m-d'];
        yield ['yy-MM-dd', 'y-m-d'];
        yield ['HH:mm:ss', 'H:i:S'];
    }
}
