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

namespace SmartSolutionsItaly\CakePHP\Database;

/**
 * Represents a single RGB color.
 * @package SmartSolutionsItaly\CakePHP\Database
 * @author Lucio Benini <dev@smartsolutions.it>
 * @since 1.0.0
 */
class Color implements \JsonSerializable
{
    /**
     * A value from 0 to 16777215 which represents a color in RGB format.
     * @var int
     * @since 1.0.0
     */
    protected $_value;

    /**
     * Color constructor.
     * @param int $value RGB value as integer from 0 to 16777215.
     * @since 1.0.0
     */
    public function __construct(int $value = 0)
    {
        $this->_value = max(min($value, 16777215), 0);
    }

    /**
     * Parses the given value and returns a Color instance.
     * @param mixed $value The value to parse. Possible types are arrays, strings and integers.
     * @return Color A Color instance using the given values.
     * @since 1.0.0
     */
    public static function parse($value): Color
    {
        $instance = new static(0);

        if (is_array($value)) {
            if (count($value) >= 3) {
                $instance = static::fromRGB((int)$value[0], (int)$value[1], (int)$value[2]);
            }
        } elseif (is_string($value)) {
            $value = str_replace('#', '', $value);

            if (strlen($value) == 3) {
                $value = str_repeat(substr($value, 0, 1), 2) . str_repeat(substr($value, 1, 1), 2) . str_repeat(substr($value, 2, 1), 2);
            }

            $instance = new static(hexdec($value));
        } elseif ($value instanceof Color) {
            $instance = new static($value->_value);
        } elseif ($value instanceof \BrianMcdo\ImagePalette\Color) {
            $instance = static::fromColor($value);
        } else {
            $instance = new static((int)$value);
        }

        return $instance;
    }

    /**
     * Creates a Color instance using the given values.
     * @param int $r RGB value from 0 to 255.
     * @param int $g RGB value from 0 to 255.
     * @param int $b RGB value from 0 to 255.
     * @return Color A Color instance created using the given value.
     * @since 1.0.0
     */
    public static function fromRGB(int $r, int $g, int $b): Color
    {
        return new static(((($r << 8) + $g) << 8) + $b);
    }

    /**
     * Creates a Color instance using the given value.
     * @param \BrianMcdo\ImagePalette\Color $color A color palette containing the RGB values.
     * @return Color A Color instance created using the given value.
     * @since 1.0.0
     */
    public static function fromColor(\BrianMcdo\ImagePalette\Color $color): Color
    {
        return static::fromRGB((int)$color->r, (int)$color->g, (int)$color->b);
    }

    /**
     * Gets the most used colors in the given image.
     * @param string $file Absolute path of the image file to process.
     * @param int $limit The limit of colors to retrieve.
     * @param int $precision Color precision.
     * @param bool $hex A value indicating whether the function should return hexadecimal colors or not. Default value "false".
     * @return array An array containing the most used colors in the given image.
     */
    public static function fromFile(string $file, int $limit = 5, int $precision = 5, bool $hex = false): array
    {
        $palette = new \BrianMcdo\ImagePalette\ImagePalette($file, $precision, $limit);
        $results = $palette->getColors($limit);

        if ($hex) {
            $colors = [];

            foreach ($results as $result) {
                $colors[] = static::fromColor($result)->toHTML();
            }

            return $colors;
        } else {
            return $results;
        }
    }

    /**
     * Converts the current Color to an hexadecimal string using the HTML standard.
     * @return string A hexadecimal string which represents the value of the current instance using the HTML standard.
     * @since 1.0.0
     */
    public function toHTML(): string
    {
        return '#' . str_pad($this->toHex(), 6, "0", STR_PAD_LEFT);
    }

    /**
     * Converts the current Color to an hexadecimal string.
     * @return string A hexadecimal string which represents the value of the current instance.
     * @since 1.0.0
     */
    public function toHex(): string
    {
        return (string)dechex($this->_value);
    }

    /**
     * Converts the current Color to a decimal number.
     * @return int A decimal number which represents the value of the current instance.
     * @since 1.0.0
     */
    public function toDecimal(): int
    {
        return (int)$this->_value;
    }

    /**
     * Converts the current Color instance to an array containing the red, blue and green values.
     * @return array An array containing the red, blue and green values of the current instance.
     * @since 1.0.0
     */
    public function toRGB(): array
    {
        return [
            'r' => ($this->_value >> 16) & 0xFF,
            'g' => ($this->_value >> 8) & 0xFF,
            'b' => $this->_value & 0xFF
        ];
    }

    /**
     * Converts the current Color instance to an array containing the hue, saturation and luma values.
     * @return array An array containing the hue, saturation and luma values of the current instance.
     * @since 1.0.0
     */
    public function toHSL(): array
    {
        $r = (($this->_value >> 16) & 0xFF) / 255;
        $g = (($this->_value >> 8) & 0xFF) / 255;
        $b = ($this->_value & 0xFF) / 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d != 0) {
            $s = $d / (1 - abs(2 * $l - 1));

            switch ($max) {
                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;
                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
                case $r:
                default:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }

                    break;
            }
        }

        return [
            'h' => $h,
            's' => $s,
            'l' => $l
        ];
    }

    /**
     * Converts the current instance in its string representation.
     * @return string A string representation of the current instance.
     * @since 1.0.0
     */
    public function __toString(): string
    {
        return $this->toHTML();
    }

    /**
     * Specify data which should be serialized to JSON.
     * @return string data which can be serialized by json_encode, which is a value of any type other than a resource.
     * @since 1.0.0
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): string
    {
        return $this->toHTML();
    }

    /**
     * Inverts the current value.
     * @return Color The current instance.
     * @since 1.0.0
     */
    public function negate(): Color
    {
        $r = 255 - (($this->_value >> 16) & 0xFF);
        $g = 255 - (($this->_value >> 8) & 0xFF);
        $b = 255 - ($this->_value & 0xFF);

        $this->_value = (int)$r;
        $this->_value = ($this->_value << 8) + (int)$g;
        $this->_value = ($this->_value << 8) + (int)$b;

        return $this;
    }

    /**
     * Modifies the current instance's value to its monochromatic version.
     * @return Color The current instance.
     * @since 1.0.0
     */
    public function monochrome(): Color
    {
        $g = ((255 - (($this->_value >> 16) & 0xFF)) + (255 - (($this->_value >> 8) & 0xFF)) + (255 - ($this->_value & 0xFF))) / 3;
        $g = $g > 128 ? 1 : 255;
        $this->_value = ((((int)$g << 8) + (int)$g) << 8) + (int)$g;

        return $this;
    }
}
