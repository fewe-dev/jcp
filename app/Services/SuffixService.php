<?php

declare(strict_types=1);

namespace App\Services;

use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class SuffixService
{
    public function __construct(protected Variables $variables) {}

    /**
     * @param array<int, array<string, null|array<int, string>|string>> $levels
     *
     * @return array<int, string>
     */
    public function parse(OutputStyle $output, array $levels): array
    {
        $suffixes = [];

        foreach ($levels as $level => $levelData) {
            $levelType = $levelData['type'];

            if ('suffix' === $levelType) {
                $levelValue = $levelData['value'];

                if (!$this->variables->isEmpty($levelValue)) {
                    $suffixes[$level] = $this->variables->stringValue($levelValue);
                }
            }
        }

        $output->writeln(sprintf('Suffixes: %s', trim(print_r($suffixes, true))), OutputInterface::VERBOSITY_VERBOSE);

        return $suffixes;
    }
}
