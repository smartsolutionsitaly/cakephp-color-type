<?php
/**
 * cakephp-color-type (https://github.com/smartsolutionsitaly/cakephp-color-type)
 * Copyright (c) Smart Solutions S.r.l. (https://smartsolutions.it)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @category  cakephp-plugin
 * @package   cakephp-color-type
 * @author    Lucio Benini <dev@smartsolutions.it>
 * @copyright 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://smartsolutions.it Smart Solutions S.r.l.
 * @since     1.0.0
 */

namespace SmartSolutionsItaly\CakePHP\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use PDO;
use SmartSolutionsItaly\CakePHP\Database\Color;

/**
 * RGB color type.
 * @package SmartSolutionsItaly\CakePHP\Database\Type
 * @author Lucio Benini <dev@smartsolutions.it>
 * @since 1.0.0
 */
class ColorType extends Type
{
    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::marshal()
     * @since 1.0.0
     */
    public function marshal($value)
    {
        return Color::parse($value);
    }

    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::toPHP()
     * @since 1.0.0
     */
    public function toPHP($value, Driver $d)
    {
        return new Color($value == null ? 0 : $value);
    }

    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::toDatabase()
     * @since 1.0.0
     */
    public function toDatabase($value, Driver $driver)
    {
        if ($value instanceof Color) {
            return $value->toDecimal();
        } else {
            return $value;
        }
    }

    /**
     * {@inheritDoc}
     * @see \Cake\Database\Type::toStatement()
     * @since 1.0.0
     */
    public function toStatement($value, Driver $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return PDO::PARAM_INT;
    }
}
