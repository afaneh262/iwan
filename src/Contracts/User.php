<?php

namespace Afaneh262\Iwan\Contracts;

interface User
{
    public function hasRole($name);

    public function hasPermission($name);

    public function hasPermissionOrFail($name);

    public function hasPermissionOrAbort($name, $statusCode = 403);
}
