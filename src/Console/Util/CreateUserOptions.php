<?php

namespace NovaKit\NovaOnVapor\Console\Util;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class CreateUserOptions
{
    /**
     * List of create user options.
     *
     * @var \Illuminate\Support\Collection<int, array>
     */
    protected $questions;

    /**
     * Construct a new Create User Options.
     *
     * @param callable(\NovaKit\NovaOnVapor\Console\Util\CreateUserOptions):array  $callback
     */
    public function __construct(callable $callback)
    {
        $this->questions = new Collection(call_user_func($callback, $this));
    }

    /**
     * Prompt the user for input.
     *
     * @param  string  $question
     * @param  string|null  $default
     * @return array
     */
    public function ask(string $question, ?string $default = null): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED, '', $default];
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param  string  $question
     * @param  array  $choices
     * @param  string|null  $default
     * @param  mixed|null  $attempts
     * @param  bool  $multiple
     * @return array
     */
    public function choice(string $question, array $choices, $default = null, $attempts = null, $multiple = false): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '', $choices];
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string  $question
     * @param  bool  $fallback
     * @return array
     */
    public function secret(string $question, bool $fallback = true): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_OPTIONAL, '', null];
    }

    /**
     * Convert to command options.
     *
     * @param  \Illuminate\Console\Command  $command
     * @return void
     */
    public function toCommandOptions(Command $command): void
    {
        $this->questions->each(function ($question) use ($command) {
            $command->addOption(...$question);
        });
    }

    /**
     * Convert to command callback.
     *
     * @param  \Illuminate\Console\Command  $command
     * @return \Closure():array<int, mixed>
     */
    public function toCommandCallback(Command $command): Closure
    {
        return function () use ($command) {
            return $this->questions->transform(function ($question) use ($command) {
                $key = $question[0];
                $variant = $question[2];
                $value = $command->hasOption($key) ? $command->option($key) : null;

                if ($variant === InputOption::VALUE_REQUIRED && is_null($value)) {
                    throw new InvalidArgumentException("Missing --{$key} option");
                }

                if ($key === 'password' && empty($value) && $variant === InputOption::VALUE_OPTIONAL) {
                    $value = config('nova-on-vapor.user.default-password') ?? Str::random(8);
                }

                return $value;
            })->all();
        };
    }

    /**
     * Parse question key.
     *
     * @param  string  $question
     * @return string
     */
    protected function parseQuestion(string $question): string
    {
        if ($question === 'Email Address') {
            return 'email';
        }

        return Str::slug($question);
    }
}
