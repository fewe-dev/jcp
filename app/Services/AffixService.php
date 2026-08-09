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
class AffixService
{
    public function __construct(protected Variables $variables) {}

    /**
     * @param array<int, array<string, null|array<int, string>|string>> $levels
     *
     * @return array<int, array<int, string>>
     */
    public function parse(OutputStyle $output, array $levels): array
    {
        $affixes = [];

        foreach ($levels as $level => $levelData) {
            $levelType = $levelData['type'];

            if ('affix' === $levelType) {
                $levelValue = $levelData['value'];

                if (!$this->variables->isEmpty($levelValue) && is_array($levelValue)) {
                    foreach ($levelValue as $levelValueValue) {
                        $affixes[$level][] = $levelValueValue;
                    }
                }
            }
        }

        $output->writeln(sprintf('Affixes: %s', trim(print_r($affixes, true))), OutputInterface::VERBOSITY_VERBOSE);

        return $affixes;
    }
}
