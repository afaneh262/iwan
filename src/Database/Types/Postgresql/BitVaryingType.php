<?php

namespace Afaneh262\Iwan\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Afaneh262\Iwan\Database\Types\Type;

class BitVaryingType extends Type
{
    const NAME = 'bit varying';
    const DBTYPE = 'varbit';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        $length = empty($field['length']) ? 255 : $field['length'];

        return "varbit({$length})";
    }
}
