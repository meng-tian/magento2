<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Math;

class DivisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getExactDivisionDataProvider
     */
    public function testGetExactDivision($dividend, $divisor, $expected)
    {
        $mathDivision = new \Magento\Math\Division();
        $remainder = $mathDivision->getExactDivision($dividend, $divisor);
        $this->assertEquals($expected, $remainder);
    }

    /**
     * @return array
     */
    public function getExactDivisionDataProvider()
    {
        return array(
            array(17, 3 , 2),
            array(7.7, 2 , 1.7),
            array(17.8, 3.2 , 1.8),
            array(11.7, 1.7 , 1.5),
            array(8, 2, 0)
        );
    }
}