<?php

it(
    'process command without file',
    function () {
        $this->artisan('process')->assertExitCode(1);
    }
);
