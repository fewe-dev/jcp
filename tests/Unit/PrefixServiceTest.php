<?php

use App\Services\PrefixService;
use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;

test(
    'Test if prefixes are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $prefixService = new PrefixService(new Variables());

        $output->expects('writeln');
        $prefixes = $prefixService->parse($output, [3 => ['type' => 'prefix', 'value' => '/tmp/prefix']]);

        expect($prefixes)->toBeArray()->and($prefixes)->toHaveCount(1)->and($prefixes)->toMatchArray(
            [3 => '/tmp/prefix']
        );
    }
);
