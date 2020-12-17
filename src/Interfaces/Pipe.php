<?php

namespace Chiiya\Common\Interfaces;

use Closure;

interface Pipe
{
    public function handle($data, Closure $next);
}
