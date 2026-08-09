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
class PlaceholderService
{
    /**
     * @param array<int, array<string, null|array<int, string>|string>> $levels
     * @param array<string, string>                                     $parameters
     *
     * @return array<int, array<string, string>>
     */
    public function parse(OutputStyle $output, array $levels, string $file, array $parameters): array
    {
        $placeHolders = [1 => ['configPath' => dirname($file)]];

        foreach ($levels as $level => $levelData) {
            $levelType = $levelData['type'];

            if ('input' === $levelType) {
                $placeHolders[$level] = $parameters;
            }
        }

        $output->writeln(
            sprintf('Parameters: %s', trim(print_r($placeHolders, true))),
            OutputInterface::VERBOSITY_VERBOSE
        );

        return $placeHolders;
    }
}
