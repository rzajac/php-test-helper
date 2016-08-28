<?php

// Trick to inject our own mysqli_report() to Kicaj\Test\Helper\Database\Driver.

namespace Kicaj\Test\Helper\Database\Driver;

class _WhatMysqliReport
{
    public static $throw = false;
}

function mysqli_report($mode)
{
    if (_WhatMysqliReport::$throw === false) {
        return \mysqli_report($mode);
    } else {
        throw new \Exception('Was not expecting call to mysqli_report');
    }
}
