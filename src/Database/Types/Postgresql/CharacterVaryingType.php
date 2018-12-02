<?php

namespace Afaneh262\Iwan\Database\Types\Postgresql;

use Afaneh262\Iwan\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    const NAME = 'character varying';
    const DBTYPE = 'varchar';
}
