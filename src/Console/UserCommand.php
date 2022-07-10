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

    protected $createUserOptions;

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

        $this->createUserOptions = new Util\CreateUserOptions(
            Nova::$createUserCommandCallback ?? function ($command) {
                return [
                    $command->ask('Name'),
                    $command->ask('Email Address'),
                    $command->secret('Password'),
                ];
            }
        );

        $this->createUserOptions->toCommandOptions($this);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        tap(Nova::$createUserCommandCallback, function ($originalCreateUserCommandCallback) {
            Nova::$createUserCommandCallback = $this->createUserOptions->toCommandCallback();

            Nova::createUser($this);

            Nova::$createUserCommandCallback = $originalCreateUserCommandCallback;
        });

        return Command::SUCCESS;
    }
}
