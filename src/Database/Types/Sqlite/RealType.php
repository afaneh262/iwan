<?php

namespace Afaneh262\Iwan\Database\Types\Sqlite;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Afaneh262\Iwan\Database\Types\Type;

class RealType extends Type
{
    const NAME = 'real';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'real';
    }
}
