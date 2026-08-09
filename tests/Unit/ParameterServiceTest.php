<?php

use App\Services\ParameterService;
use Illuminate\Console\OutputStyle;

test(
    'Test if parameter are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $parameterService = new ParameterService();

        $output->expects('writeln');
        $parameters = $parameterService->parse($output, ['test:test', 'test2:test2']);

        expect($parameters)->toBeArray()->and($parameters)->toHaveCount(2)->and($parameters)->toMatchArray(
            ['test' => 'test', 'test2' => 'test2']
        );
    }
);
