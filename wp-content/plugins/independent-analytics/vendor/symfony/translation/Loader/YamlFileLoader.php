<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace IAWP_SCOPED\Symfony\Component\Translation\Loader;

use IAWP_SCOPED\Symfony\Component\Translation\Exception\InvalidResourceException;
use IAWP_SCOPED\Symfony\Component\Translation\Exception\LogicException;
use IAWP_SCOPED\Symfony\Component\Yaml\Exception\ParseException;
use IAWP_SCOPED\Symfony\Component\Yaml\Parser as YamlParser;
use IAWP_SCOPED\Symfony\Component\Yaml\Yaml;
/**
 * YamlFileLoader loads translations from Yaml files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlFileLoader extends FileLoader
{
    private $yamlParser;
    /**
     * {@inheritdoc}
     */
    protected function loadResource(string $resource)
    {
        if (null === $this->yamlParser) {
            if (!\class_exists(\IAWP_SCOPED\Symfony\Component\Yaml\Parser::class)) {
                throw new LogicException('Loading translations from the YAML format requires the Symfony Yaml component.');
            }
            $this->yamlParser = new YamlParser();
        }
        try {
            $messages = $this->yamlParser->parseFile($resource, Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new InvalidResourceException(\sprintf('The file "%s" does not contain valid YAML: ', $resource) . $e->getMessage(), 0, $e);
        }
        if (null !== $messages && !\is_array($messages)) {
            throw new InvalidResourceException(\sprintf('Unable to load file "%s".', $resource));
        }
        return $messages ?: [];
    }
}
