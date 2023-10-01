<?php

declare(strict_types=1);

// этот файл нужен для phpstan-symfony
// https://github.com/phpstan/phpstan-symfony#analysis-of-symfony-console-commands

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

assert(is_string($_SERVER['APP_ENV']));
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);

return new Application($kernel);
