<?php

namespace Afaneh262\Iwan\Database\Types\Postgresql;

use Afaneh262\Iwan\Database\Types\Common\DoubleType;

class DoublePrecisionType extends DoubleType
{
    const NAME = 'double precision';
    const DBTYPE = 'float8';
}
