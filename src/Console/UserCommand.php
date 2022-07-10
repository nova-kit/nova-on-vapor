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

    protected $originalCreateUserCommandCallback;

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

        $this->originalCreateUserCommandCallback = Nova::$createUserCommandCallback;

        $this->createUserOptions = new Util\CreateUserOptions(
            $this->originalCreateUserCommandCallback ?? function ($command) {
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
        $this->input->setInteractive(false);

        Nova::$createUserCommandCallback = $this->createUserOptions->toCommandCallback($this);

        Nova::createUser($this);

        Nova::$createUserCommandCallback = $this->originalCreateUserCommandCallback;

        return Command::SUCCESS;
    }
}
