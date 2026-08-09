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
class ParameterService
{
    /**
     * @param array<int,string> $list
     *
     * @return array<string,string>
     */
    public function parse(OutputStyle $output, array $list): array
    {
        $parameters = [];

        foreach ($list as $value) {
            [$paramKey, $paramValue] = explode(':', $value, 2);

            $parameters[$paramKey] = $paramValue;
        }

        $output->writeln(
            sprintf('Parameters: %s', trim(print_r($parameters, true))),
            OutputInterface::VERBOSITY_VERBOSE
        );

        return $parameters;
    }
}
