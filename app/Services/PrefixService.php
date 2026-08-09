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
class PrefixService
{
    public function __construct(protected Variables $variables) {}

    /**
     * @param array<int, array<string, null|array<int, string>|string>> $levels
     *
     * @return array<int, string>
     */
    public function parse(OutputStyle $output, array $levels): array
    {
        $prefixes = [];

        foreach ($levels as $level => $levelData) {
            $levelType = $levelData['type'];

            if ('prefix' === $levelType) {
                $levelValue = $levelData['value'];

                if (!$this->variables->isEmpty($levelValue)) {
                    $prefixes[$level] = $this->variables->stringValue($levelValue);
                }
            }
        }

        $output->writeln(sprintf('Prefixes: %s', trim(print_r($prefixes, true))), OutputInterface::VERBOSITY_VERBOSE);

        return $prefixes;
    }
}
