<?php
/*
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
*/
declare(strict_types = 1);

use Phalcon\Di\Injectable;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\Adapter\NativeArray;

class Translate extends Injectable
{
    private $_trans;

    function __construct($language)
    {
        $file = __DIR__ . '/languages/' . strtolower($language) . '.php';
        if (is_readable($file)) {
            require $file;
            $interpolator = new InterpolatorFactory();
            $this->_trans = new NativeArray($interpolator, $options);
        }
    }
    public function from($sentence)
    {
        $native = $sentence;
        if ($this->_trans) {
            $native = $this->_trans->t($sentence);
        }
        return $native;
    }
}
