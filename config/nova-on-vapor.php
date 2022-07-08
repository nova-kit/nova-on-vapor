<?php

use Symfony\Component\Console\Input\InputOption;

return [
    'user' => [
        ['name', null, InputOption::VALUE_REQUIRED, "The user's name"],
        ['email', null, InputOption::VALUE_REQUIRED, "The user's e-mail address"],
        ['password', null, InputOption::VALUE_OPTIONAL, "The user's password"],
    ],
];
