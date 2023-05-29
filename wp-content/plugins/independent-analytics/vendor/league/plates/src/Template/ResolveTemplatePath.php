<?php

namespace IAWP_SCOPED\League\Plates\Template;

use IAWP_SCOPED\League\Plates\Exception\TemplateNotFound;
interface ResolveTemplatePath
{
    /**
     * @throws TemplateNotFound if the template could not be properly resolved to a file path
     */
    public function __invoke(Name $name) : string;
}
