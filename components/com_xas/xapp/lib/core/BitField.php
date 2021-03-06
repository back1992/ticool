<?php
/**
 * @version 0.1.0
 * @link http://www.xapp-studio.com
 * @package Math
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class BitField {

    private $value;

    public function __construct($value=0) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function get($n) {
        return ($this->value & $n) == $n;
    }

    public function set($n) {
        $this->value |= $n;
    }

    public function clear($n) {
        $this->value &= ~$n;
    }
}