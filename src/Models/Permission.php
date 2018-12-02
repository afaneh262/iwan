<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\Model;
use Afaneh262\Iwan\Facades\Iwan;
use Afaneh262\Iwan\Traits\HasRelationships;

class Permission extends Model
{
    use HasRelationships;

    protected $guarded = [];

    public function roles()
    {
        return $this->hasMany(Iwan::modelClass('Role'));
    }

    public static function generateFor($table_name)
    {
        self::firstOrCreate(['key' => 'browse_'.$table_name, 'table_name' => $table_name]);
        self::firstOrCreate(['key' => 'read_'.$table_name, 'table_name' => $table_name]);
        self::firstOrCreate(['key' => 'edit_'.$table_name, 'table_name' => $table_name]);
        self::firstOrCreate(['key' => 'add_'.$table_name, 'table_name' => $table_name]);
        self::firstOrCreate(['key' => 'delete_'.$table_name, 'table_name' => $table_name]);
    }

    public static function removeFrom($table_name)
    {
        self::where(['table_name' => $table_name])->delete();
    }
}
