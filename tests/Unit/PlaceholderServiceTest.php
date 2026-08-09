<?php

use App\Services\ParameterService;
use App\Services\PlaceholderService;
use Illuminate\Console\OutputStyle;

test(
    'Test if placeholders are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $parameterService = new ParameterService();

        $output->expects('writeln');
        $parameters = $parameterService->parse($output, ['test:test', 'test2:test2']);

        $placeholderService = new PlaceholderService();

        $output->expects('writeln');
        $placeholders = $placeholderService->parse($output, [2 => ['type' => 'input']], 'tests/test.json', $parameters);

        expect($placeholders)->toBeArray()->and($placeholders)->toHaveCount(2)->and($placeholders)->toMatchArray(
            [1 => ['configPath' => 'tests'], 2 => ['test' => 'test', 'test2' => 'test2']]
        );
    }
);
