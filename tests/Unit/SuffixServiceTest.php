<?php

use App\Services\SuffixService;
use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;

test(
    'Test if suffixes are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $suffixService = new SuffixService(new Variables());

        $output->expects('writeln');
        $suffixes = $suffixService->parse($output, [4 => ['type' => 'suffix', 'value' => '/tmp/suffix']]);

        expect($suffixes)->toBeArray()->and($suffixes)->toHaveCount(1)->and($suffixes)->toMatchArray(
            [4 => '/tmp/suffix']
        );
    }
);
