<?php

use App\Services\AffixService;
use FeWeDev\Base\Variables;
use Illuminate\Console\OutputStyle;

test(
    'Test if affixes are parsed correctly',
    function () {
        $output = Mockery::mock(OutputStyle::class);

        $affixService = new AffixService(new Variables());

        $output->expects('writeln');
        $affixes = $affixService->parse($output, [5 => ['type' => 'affix', 'value' => ['/tmp/prefix', '/tmp/suffix']]]);

        expect($affixes)->toBeArray()->and($affixes)->toHaveCount(1)->and($affixes)->toMatchArray(
            [5 => ['/tmp/prefix', '/tmp/suffix']]
        );
    }
);
