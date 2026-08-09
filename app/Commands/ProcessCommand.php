<?php

namespace App\Commands;

use App\Exceptions\CommandException;
use App\Services\AffixService;
use App\Services\JsonService;
use App\Services\LevelService;
use App\Services\ParameterService;
use App\Services\PlaceholderService;
use App\Services\PrefixService;
use App\Services\ProcessService;
use App\Services\SuffixService;
use FeWeDev\Base\Variables;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ProcessCommand extends BaseCommand
{
    public function __construct(
        protected ParameterService $parameterService,
        protected LevelService $levelService,
        protected PlaceholderService $placeholderService,
        protected PrefixService $prefixService,
        protected SuffixService $suffixService,
        protected AffixService $affixService,
        protected ProcessService $processService,
        protected JsonService $jsonService,
        Variables $variables
    ) {
        parent::__construct($variables);
    }

    protected function getCommandName(): string
    {
        return 'process';
    }

    protected function getCommandDescription(): string
    {
        return 'Process a JSON configuration file.';
    }

    protected function getCommandParameters(): array
    {
        return [
            $this->prepareInputOption(
                'file',
                'The JSON configuration file.',
                false
            ),
            $this->prepareInputOption(
                'param',
                'Additional parameters.',
                true
            ),
            $this->prepareInputOption(
                'level',
                'Information how to treat replacement levels',
                true
            ),
        ];
    }

    protected function executeCommand(): int
    {
        $file = $this->getRequiredOption('file', 'No file to process specified!');

        $paramInput = $this->getOptionList('param');
        $parameters = $this->parameterService->parse($this->getOutput(), $paramInput);

        $levelInput = $this->getOptionList('level');
        $levels = $this->levelService->parse($this->getOutput(), $levelInput);

        $placeHolders = $this->placeholderService->parse($this->getOutput(), $levels, $file, $parameters);
        $prefixes = $this->prefixService->parse($this->getOutput(), $levels);
        $suffixes = $this->suffixService->parse($this->getOutput(), $levels);
        $affixes = $this->affixService->parse($this->getOutput(), $levels);

        try {
            $config = $this->processService->processConfig(
                $this->getOutput(),
                $file,
                $placeHolders,
                $prefixes,
                $suffixes,
                $affixes
            );
        } catch (CommandException $exception) {
            return $this->exitWithError($exception->getMessage());
        }

        foreach ($levels as $level => $levelData) {
            $level = (int) $level;
            $levelType = $levelData['type'];

            if ('object' === $levelType) {
                $levelValue = $levelData['value'];

                if (!$this->variables->isEmpty($levelValue) && is_string($levelValue)
                    && array_key_exists($levelValue, $config)) {
                    $configLevelValues = $config[$levelValue];

                    if (is_array($configLevelValues)) {
                        foreach ($configLevelValues as $key => $value) {
                            $placeHolders[$level][$this->variables->stringValue($key)]
                                = $this->variables->stringValue($value);
                        }
                    }
                }
            }
        }

        try {
            $config = $this->processService->processValues(
                $config,
                [],
                false,
                $placeHolders,
                $prefixes,
                $suffixes,
                $affixes
            );
        } catch (CommandException $exception) {
            return $this->exitWithError($exception->getMessage());
        }

        $this->output->writeln($this->jsonService->outputConfig($config));

        return self::SUCCESS;
    }
}
