<?php

namespace IAWP_SCOPED\League\Plates\Extension;

use IAWP_SCOPED\League\Plates\Engine;
/**
 * A common interface for extensions.
 */
interface ExtensionInterface
{
    public function register(Engine $engine);
}
