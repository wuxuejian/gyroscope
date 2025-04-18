<?php

namespace App\Http\Controller\AdminApi\Open;

use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('ent/openapi')]
#[Middleware(['auth.admin', 'ent.auth', 'ent.log'])]
class OpenDocController
{

}
