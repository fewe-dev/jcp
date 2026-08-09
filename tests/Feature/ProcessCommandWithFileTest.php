<?php

it(
    'process command with file',
    function () {
        $this->artisan(
            implode(
                ' ',
                [
                    'process',
                    '--file tests/test.json',
                    '--param value1:value1',
                    '--param value2:value2',
                    '--level 1:object:global',
                    '--level 2:input',
                    '--level 3:prefix:/tmp/prefix',
                    '--level 4:suffix:/tmp/suffix',
                    '--level 5:affix:/tmp/prefix:/tmp/suffix'
                ]
            )
        )->assertSuccessful();
    }
);
