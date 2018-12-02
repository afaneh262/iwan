<?php

namespace Afaneh262\Iwan\Database\Types\Postgresql;

use Afaneh262\Iwan\Database\Types\Common\CharType;

class CharacterType extends CharType
{
    const NAME = 'character';
    const DBTYPE = 'bpchar';
}
