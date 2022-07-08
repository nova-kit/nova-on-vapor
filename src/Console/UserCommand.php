<?php

namespace NovaKit\NovaOnVapor\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Nova\Nova;
use Symfony\Component\Console\Input\InputOption;

class UserCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nova:vapor-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    protected $createUserCommandOptions;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->setName($this->name)
            ->setDescription($this->description);

        $this->createUserCommandOptions = collect(config('nova-on-vapor.user'))
            ->each(function ($option) {
                $this->addOption(...$option);
            });
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Nova::$createUserCommandCallback = function ($command) {
            return $this->createUserCommandOptions->transform(function ($option) {
                $key = $option[0];
                $variant = $option[2];
                $value = $this->hasOption($key) ? $this->option($key) : null;

                if ($variant === InputOption::VALUE_REQUIRED && is_null($value)) {
                    throw new InvalidArgumentException("Missing --{$key} option");
                }

                if ($key === 'password' && empty($value) && $variant === InputOption::VALUE_OPTIONAL) {
                    $value = Str::random(8);
                }

                return $value;
            })->all();
        };

        call_user_func(Nova::$createUserCommandCallback, $this);

        // Nova::createUser($this);

        return Command::SUCCESS;
    }
}
