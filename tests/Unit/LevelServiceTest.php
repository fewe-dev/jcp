<?php

use App\Services\LevelService;
use Illuminate\Console\OutputStyle;

test(
    'Test if levels are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $levelService = new LevelService();

        $output->expects('writeln');
        $levels = $levelService->parse(
            $output,
            [
                '1:object:global',
                '2:input',
                '3:prefix:/tmp/prefix',
                '4:suffix:/tmp/suffix',
                '5:affix:/tmp/prefix:/tmp/suffix'
            ]
        );

        expect($levels)->toBeArray()->and($levels)->toHaveCount(5)->and($levels)->toMatchArray(
            [
                1 => ['type' => 'object', 'value' => 'global'],
                2 => ['type' => 'input', 'value' => null],
                3 => ['type' => 'prefix', 'value' => '/tmp/prefix'],
                4 => ['type' => 'suffix', 'value' => '/tmp/suffix'],
                5 => ['type' => 'affix', 'value' => ['/tmp/prefix', '/tmp/suffix']],
            ]
        );
    }
);
