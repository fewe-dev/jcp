<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConfigException;
use App\Exceptions\ValueException;
use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ProcessService
{
    public function __construct(
        protected Variables $variables,
        protected Arrays $arrays,
        protected JsonService $jsonService
    ) {}

    /**
     * @param array<int, array<string, string>> $placeHolders
     * @param array<int, string>                $prefixes
     * @param array<int, string>                $suffixes
     * @param array<int, array<int, string>>    $affixes
     *
     * @return array<mixed>
     */
    public function processConfig(
        OutputStyle $output,
        string $file,
        array $placeHolders,
        array $prefixes,
        array $suffixes,
        array $affixes,
        ?string $configPath = null
    ): array {
        $config = $this->jsonService->loadConfig($output, $file);

        if (null === $configPath) {
            $configPath = dirname($file);
        }

        return $this->processIncludes(
            $output,
            $this->processRequirements(
                $output,
                $config,
                $placeHolders,
                $prefixes,
                $suffixes,
                $affixes,
                $configPath
            ),
            $placeHolders,
            $prefixes,
            $suffixes,
            $affixes,
            $configPath
        );
    }

    /**
     * @param array<mixed>                      $config
     * @param array<int, string>                $key
     * @param array<int, array<string, string>> $placeHolders
     * @param array<int, string>                $prefixes
     * @param array<int, string>                $suffixes
     * @param array<int, array<int, string>>    $affixes
     *
     * @return array<mixed>
     */
    public function processValues(
        array $config,
        array $key,
        bool $isList,
        array $placeHolders,
        array $prefixes,
        array $suffixes,
        array $affixes
    ): array {
        $configValues = 0 === count($key) ? $config : $this->arrays->getValue($config, implode(':', $key));

        /** @var array<mixed> $parentConfigValues */
        $parentConfigValues
            = $isList && count($key) > 1 ? $this->arrays->getValue($config, implode(':', array_slice($key, 0, -1))) : [];

        if (is_array($configValues)) {
            foreach ($configValues as $configKey => $configValue) {
                $fullConfigKey = array_merge($key, [$configKey]);

                if (is_scalar($configValue)) {
                    $configValue = (string) $configValue;

                    do {
                        preg_match_all('/\{.*?\}+/', $configValue, $matches, PREG_OFFSET_CAPTURE);

                        $matches = array_shift($matches);
                        $matchesCount = count($matches);

                        if ($matchesCount > 0) {
                            $match = array_shift($matches);
                            [$matchFullValue, $matchOffset] = $match;
                            $matchLength = strlen($matchFullValue);
                            preg_match('/(\{+)(.*?)(\}+)/', $matchFullValue, $matchParts);

                            if (count($matchParts) > 0) {
                                $matchLevel = strlen($matchParts[1]);
                                $matchValue = $matchParts[2];

                                if (array_key_exists($matchLevel, $placeHolders)) {
                                    if (array_key_exists($matchValue, $placeHolders[$matchLevel])) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            $this->variables->stringValue($placeHolders[$matchLevel][$matchValue]),
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } elseif (1 === $matchLevel && array_key_exists($matchValue, $configValues)) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            $this->variables->stringValue($configValues[$matchValue]),
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } elseif (1 === $matchLevel && array_key_exists($matchValue, $parentConfigValues)) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            $this->variables->stringValue($parentConfigValues[$matchValue]),
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } elseif (str_contains($matchValue, ':')) {
                                        $configParsedValue = $this->arrays->getValue($config, $matchValue);

                                        if (null === $configParsedValue) {
                                            throw new ValueException(
                                                sprintf('Could not replace placeholder: %s', $matchFullValue)
                                            );
                                        }

                                        $configValue = substr_replace(
                                            $configValue,
                                            $this->variables->stringValue($configParsedValue),
                                            $matchOffset,
                                            $matchLength
                                        );
                                        //$configValue = $this->variables->stringValue($configValue);
                                    } else {
                                        throw new ValueException(
                                            sprintf('Could not replace placeholder: %s', $matchFullValue)
                                        );
                                    }
                                } elseif (array_key_exists($matchLevel, $prefixes)) {
                                    $prefix = $prefixes[$matchLevel];

                                    if (preg_match('/^\s*\'.*?\'\s*$/', $matchValue)) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            $prefix.trim($matchValue, '\''),
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } else {
                                        $found = false;

                                        foreach ($placeHolders as $levelPlaceholders) {
                                            if (array_key_exists($matchValue, $levelPlaceholders)) {
                                                $configValue = substr_replace(
                                                    $configValue,
                                                    $prefix.$levelPlaceholders[$matchValue],
                                                    $matchOffset,
                                                    $matchLength
                                                );

                                                $found = true;

                                                break;
                                            }
                                        }

                                        if (false === $found) {
                                            throw new ValueException(
                                                sprintf('Could not replace prefix: %s', $matchFullValue)
                                            );
                                        }
                                    }
                                } elseif (array_key_exists($matchLevel, $suffixes)) {
                                    $suffix = $suffixes[$matchLevel];

                                    if (preg_match('/^\s*\'.*?\'\s*$/', $matchValue)) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            trim($matchValue, '\'').$suffix,
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } else {
                                        $found = false;

                                        foreach ($placeHolders as $levelPlaceholders) {
                                            if (array_key_exists($matchValue, $levelPlaceholders)) {
                                                $configValue = substr_replace(
                                                    $configValue,
                                                    $levelPlaceholders[$matchValue].$suffix,
                                                    $matchOffset,
                                                    $matchLength
                                                );

                                                $found = true;

                                                break;
                                            }
                                        }

                                        if (false === $found) {
                                            throw new ValueException(
                                                sprintf('Could not replace suffix: %s', $matchFullValue)
                                            );
                                        }
                                    }
                                } elseif (array_key_exists($matchLevel, $affixes)) {
                                    [$prefix, $suffix] = $affixes[$matchLevel];

                                    if (preg_match('/^\s*\'.*?\'\s*$/', $matchValue)) {
                                        $configValue = substr_replace(
                                            $configValue,
                                            $prefix.trim($matchValue, '\'').$suffix,
                                            $matchOffset,
                                            $matchLength
                                        );
                                    } else {
                                        $found = false;

                                        foreach ($placeHolders as $levelPlaceholders) {
                                            if (array_key_exists($matchValue, $levelPlaceholders)) {
                                                $configValue = substr_replace(
                                                    $configValue,
                                                    $levelPlaceholders[$matchValue].$suffix,
                                                    $matchOffset,
                                                    $matchLength
                                                );

                                                $found = true;

                                                break;
                                            }
                                        }

                                        if (false === $found) {
                                            throw new ValueException(
                                                sprintf('Could not replace affix: %s', $matchFullValue)
                                            );
                                        }
                                    }
                                } elseif (1 === $matchLevel && array_key_exists($matchValue, $configValues)) {
                                    $configValue = substr_replace(
                                        $configValue,
                                        $this->variables->stringValue($configValues[$matchValue]),
                                        $matchOffset,
                                        $matchLength
                                    );
                                } elseif (1 === $matchLevel && array_key_exists($matchValue, $parentConfigValues)) {
                                    $configValue = substr_replace(
                                        $configValue,
                                        $this->variables->stringValue($parentConfigValues[$matchValue]),
                                        $matchOffset,
                                        $matchLength
                                    );
                                } else {
                                    throw new ValueException(
                                        sprintf('Could not process value: %s', $matchFullValue)
                                    );
                                }

                                $config = $this->arrays->addDeepValue($config, $fullConfigKey, $configValue);
                            }
                        }
                    } while ($matchesCount > 0);
                } elseif (is_array($configValue)) {
                    if ($this->arrays->isAssociative($configValue)) {
                        $config = $this->processValues(
                            $config,
                            $fullConfigKey,
                            false,
                            $placeHolders,
                            $prefixes,
                            $suffixes,
                            $affixes
                        );
                    } else {
                        $config = $this->processValues(
                            $config,
                            $fullConfigKey,
                            true,
                            $placeHolders,
                            $prefixes,
                            $suffixes,
                            $affixes
                        );
                    }
                } else {
                    throw new ValueException(sprintf('Could not process key: %s', implode(':', $fullConfigKey)));
                }
            }
        } else {
            throw new ValueException(sprintf('Could not process key: %s', implode(':', $key)));
        }

        return $config;
    }

    /**
     * @param array<mixed>                      $config
     * @param array<int, array<string, string>> $placeHolders
     * @param array<int, string>                $prefixes
     * @param array<int, string>                $suffixes
     * @param array<int, array<int, string>>    $affixes
     *
     * @return array<mixed>
     */
    private function processIncludes(
        OutputStyle $output,
        array $config,
        array $placeHolders,
        array $prefixes,
        array $suffixes,
        array $affixes,
        string $basePath
    ): array {
        if (array_key_exists('include', $config)) {
            $config = $this->processValues(
                $config,
                ['include'],
                true,
                $placeHolders,
                $prefixes,
                $suffixes,
                $affixes
            );

            $includes = $config['include'];
            unset($config['include']);

            if (is_array($includes)) {
                foreach ($includes as $include) {
                    $include = $this->variables->stringValue($include);

                    if (file_exists($include)) {
                        $includeConfig = $this->processConfig(
                            $output,
                            $include,
                            $placeHolders,
                            $prefixes,
                            $suffixes,
                            $affixes,
                            $basePath
                        );
                    } else {
                        $configFile = sprintf('%s/%s', $basePath, $include);

                        if (file_exists($configFile)) {
                            $includeConfig = $this->processConfig(
                                $output,
                                $configFile,
                                $placeHolders,
                                $prefixes,
                                $suffixes,
                                $affixes,
                                $basePath
                            );
                        } else {
                            throw new ConfigException(sprintf('Could not find included config file: %s', $include));
                        }
                    }

                    $config = $this->arrays->mergeArrays($config, $includeConfig);
                }
            }
        }

        return $config;
    }

    /**
     * @param array<mixed>                      $config
     * @param array<int, array<string, string>> $placeHolders
     * @param array<int, string>                $prefixes
     * @param array<int, string>                $suffixes
     * @param array<int, array<int, string>>    $affixes
     *
     * @return array<mixed>
     */
    private function processRequirements(
        OutputStyle $output,
        array $config,
        array $placeHolders,
        array $prefixes,
        array $suffixes,
        array $affixes,
        string $configPath
    ): array {
        if (array_key_exists('require', $config)) {
            $config = $this->processValues(
                $config,
                ['require'],
                true,
                $placeHolders,
                $prefixes,
                $suffixes,
                $affixes
            );

            $requires = $config['require'];
            unset($config['require']);

            if (is_array($requires)) {
                foreach ($requires as $require) {
                    $require = $this->variables->stringValue($require);

                    if (file_exists($require)) {
                        $requireConfig = $this->processConfig(
                            $output,
                            $require,
                            $placeHolders,
                            $prefixes,
                            $suffixes,
                            $affixes,
                            $configPath
                        );
                    } else {
                        $configFile = sprintf('%s/%s', $configPath, $require);

                        if (file_exists($configFile)) {
                            $requireConfig = $this->processConfig(
                                $output,
                                $configFile,
                                $placeHolders,
                                $prefixes,
                                $suffixes,
                                $affixes,
                                $configPath
                            );
                        } else {
                            throw new ConfigException(sprintf('Could not find required config file: %s', $configFile));
                        }
                    }

                    $config = $this->arrays->mergeArrays($requireConfig, $config);
                }
            }
        }

        return $config;
    }
}
