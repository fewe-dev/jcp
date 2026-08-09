<?php

use App\Services\JsonService;
use App\Services\ProcessService;
use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;

test(
    'Test if config values are processed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $processService = new ProcessService(new Variables(), new Arrays(), new JsonService(new Json()));

        $output->expects('writeln');
        $output->expects('writeln');
        $output->expects('writeln');
        $config = $processService->processConfig(
            $output,
            'tests/test.json',
            [
                1 => [
                    'configPath' => 'tests',
                    'param1' => '{param2}',
                    'param2' => '{{value1}}'
                ],
                2 => ['value1' => 'value1', 'value2' => 'value2']
            ],
            [3 => '/tmp/prefix'],
            [4 => '/tmp/suffix'],
            [5 => ['/tmp/prefix', '/tmp/suffix']],
        );

        expect($config)->toBeArray()->and($config)->toHaveCount(3)->and($config)->toMatchArray(
            [
                'global' => ['param1' => '{param2}', 'param2' => '{{value1}}'],
                'test' => ['param1' => '{{value2}}', 'param2' => '{param1}', 'param3' => '{param1}'],
                'test2' => ['param1' => '{test:param1}']
            ]
        );

        $config = $processService->processValues(
            [
                'global' => ['param1' => '{param2}', 'param2' => '{{value1}}'],
                'test' => ['param1' => '{{value2}}', 'param2' => '{param1}', 'param3' => '{param1}'],
                'test2' => ['param1' => '{test:param1}']
            ],
            [],
            false,
            [
                1 => [
                    'configPath' => 'tests',
                    'param1' => '{param2}',
                    'param2' => '{{value1}}'
                ],
                2 => ['value1' => 'value1', 'value2' => 'value2']
            ],
            [3 => '/tmp/prefix'],
            [4 => '/tmp/suffix'],
            [5 => ['/tmp/prefix', '/tmp/suffix']]
        );

        expect($config)->toBeArray()->and($config)->toHaveCount(3)->and($config)->toMatchArray(
            [
                'global' => ['param1' => 'value1', 'param2' => 'value1'],
                'test' => ['param1' => 'value2', 'param2' => 'value1', 'param3' => 'value1'],
                'test2' => ['param1' => 'value2']
            ]
        );
    }
);
