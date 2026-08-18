<?php

use App\Services\JsonService;
use FeWeDev\Base\Json;
use Illuminate\Console\OutputStyle;

test(
    'JSON service can load and output a config file',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $json = new JsonService(new Json());

        $output->expects('writeln');
        $config = $json->loadConfig($output, 'tests/test.json');

        expect($config)->toBeArray()->and($config)->toHaveCount(4)->and($config)->toMatchArray(
            [
                'require' => ['{configPath}/test-require.json'],
                'include' => ['{configPath}/test-include.json'],
                'global' => [
                    'param2' => '{{value1}}',
                    'param3' => 'prefix {{value1}}',
                    'param4' => '{{value1}} suffix',
                    'param5' => 'af {{value1}} fix'
                ],
                'test' => ['param1' => '{{value2}}', 'param2' => '{param1}']
            ]
        );

        $output = $json->outputConfig($config);

        expect($output)->toBeString();
    }
);
