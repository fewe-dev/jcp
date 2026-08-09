<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class LevelService
{
    /**
     * @param array<int, string> $list
     *
     * @return array<int, array<string, null|array<int, string>|string>>
     */
    public function parse(OutputStyle $output, array $list): array
    {
        $levels = [];

        foreach ($list as $value) {
            $levelParts = explode(':', $value);
            $level = (int) array_shift($levelParts);
            $levelType = array_shift($levelParts);
            $levelValue
                = 0 === count($levelParts) ? null : (1 === count($levelParts) ? array_shift($levelParts) : $levelParts);

            $levels[$level] = ['type' => $levelType, 'value' => $levelValue];
        }

        $output->writeln(sprintf('Levels: %s', trim(print_r($levels, true))), OutputInterface::VERBOSITY_VERBOSE);

        return $levels;
    }
}
