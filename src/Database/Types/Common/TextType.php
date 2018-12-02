<?php

namespace Afaneh262\Iwan\Database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Afaneh262\Iwan\Database\Types\Type;

class TextType extends Type
{
    const NAME = 'text';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'text';
    }
}
