<?php

namespace NovaKit\NovaOnVapor\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Laravel\Nova\Nova;
use Laravel\Nova\Util;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\TextPrompt;
use NovaKit\NovaOnVapor\Console\Util\CreateUserOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * The create user options instance.
     *
     * @var \NovaKit\NovaOnVapor\Console\Util\CreateUserOptions
     */
    protected $createUserOptions;

    /**
     * The original create user command callback.
     *
     * @var (\Closure(\Illuminate\Console\Command): array)|null
     */
    protected $originalCreateUserCommandCallback;

    /** {@inheritDoc} */
    #[\Override]
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->setName($this->name)
            ->setDescription($this->description);

       // if (class_exists(Prompt::class) && method_exists(Prompt::class, 'interactive')) {
            Prompt::interactive(false);
        //}
    }

    /** {@inheritDoc} */
    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output)
    {

        $input->setInteractive(false);
    }

    /** {@inheritDoc} */
    #[\Override]
    protected function specifyParameters()
    {
        if (class_exists(Prompt::class)) {
            Prompt::fallbackWhen(true);
        }

        $this->originalCreateUserCommandCallback = Nova::$createUserCommandCallback;

        $this->createUserOptions = new CreateUserOptions(
            $this->originalCreateUserCommandCallback ?? static::defaultCreateUserCommandCallback()
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

        Nova::createUserUsing(
            $this->createUserOptions->toCommandCallback($this),
            Nova::$createUserCallback ?? static::defaultCreateUserCallback()
        );

        Nova::createUser($this);

        Nova::$createUserCommandCallback = $this->originalCreateUserCommandCallback;

        return Command::SUCCESS;
    }

    /**
     * Get the default callback used for the create user command.
     *
     * @return \Closure(\NovaKit\NovaOnVapor\Console\Util\CreateUserOptions):array
     */
    protected static function defaultCreateUserCommandCallback()
    {
        return function ($command) {
            return [
                $command->ask('Name'),
                $command->ask('Email Address'),
                $command->secret('Password'),
            ];
        };
    }

    /**
     * Get the default callback used for creating new Nova users.
     *
     * @return \Closure(string, string, string):\Illuminate\Database\Eloquent\Model
     */
    protected static function defaultCreateUserCallback()
    {
        return function ($name, $email, $password) {
            $model = Util::userModel();

            return tap((new $model())->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]))->save();
        };
    }
}
