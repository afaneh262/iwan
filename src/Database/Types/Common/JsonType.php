<?php

namespace Afaneh262\Iwan\Database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Afaneh262\Iwan\Database\Types\Type;

class JsonType extends Type
{
    const NAME = 'json';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'json';
    }
}
