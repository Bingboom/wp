<?php

namespace IAWP_SCOPED\Illuminate\Contracts\Container;

use Exception;
use IAWP_SCOPED\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
