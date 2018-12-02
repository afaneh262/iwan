<?php

namespace Afaneh262\Iwan\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Afaneh262\Iwan\Database\Types\Type;

class LongBlobType extends Type
{
    const NAME = 'longblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'longblob';
    }
}
