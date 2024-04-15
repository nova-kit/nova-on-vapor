<?php

namespace NovaKit\NovaOnVapor\Console\Util;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Prompts\Prompt;
use Symfony\Component\Console\Input\InputOption;

class CreateUserOptions
{
    /**
     * List of create user options.
     *
     * @var \Illuminate\Support\Collection<int, array>|null
     */
    protected $questions = null;

    /**
     * The questions callback.
     *
     * @var callable(\NovaKit\NovaOnVapor\Console\Util\CreateUserOptions):array
     */
    protected $questionsCallback;

    /**
     * Construct a new Create User Options.
     *
     * @param  callable(\NovaKit\NovaOnVapor\Console\Util\CreateUserOptions):array  $questionsCallback
     */
    public function __construct(callable $questionsCallback)
    {
        $this->questionsCallback = $questionsCallback;
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string|bool|null  $default
     * @return array
     */
    public function confirm(string $question, $default = false)
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_NONE, ''];
    }

    /**
     * Prompt the user for input.
     */
    public function ask(string $question, ?string $default = null): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED, '', $default];
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param  string|null  $default
     * @param  mixed|null  $attempts
     * @param  bool  $multiple
     */
    public function choice(string $question, array $choices, $default = null, $attempts = null, $multiple = false): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '', $choices];
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     */
    public function secret(string $question, bool $fallback = true): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_OPTIONAL, '', null];
    }

    /**
     * Convert to command options.
     */
    public function toCommandOptions(Command $command): void
    {
        $this->resolveQuestions()->each(function ($question) use ($command) {
            if (is_array($question)) {
                $command->addOption(...$question);
            }
        });
    }

    /**
     * Convert to command callback.
     *
     * @return \Closure():array<int, mixed>
     */
    public function toCommandCallback(Command $command): Closure
    {
        return function () use ($command) {
            return $this->resolveQuestions()->transform(function ($question) use ($command) {
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
     */
    protected function parseQuestion(string $question): string
    {
        if ($question === 'Email Address') {
            return 'email';
        }

        return Str::slug($question);
    }

    /**
     * Resolve the available questions.
     *
     * @return \Illuminate\Support\Collection<int, array>
     */
    protected function resolveQuestions(): Collection
    {
        if (is_null($this->questions)) {
            $this->questions = Collection::make(call_user_func($this->questionsCallback, $this));
        }

        return $this->questions;
    }
}
