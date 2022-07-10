<?php

namespace NovaKit\NovaOnVapor\Console\Util;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

class CreateUserOptions
{
    protected $questions;

    public function __construct(callable $callback)
    {
        $this->questions = collect(call_user_func($callback, $this));
    }

    public function ask(string $question, $default = null): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED, '', $default];
    }

    public function choice(string $question, array $choices, $default = null, $attempts = null, $multiple = false): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '', $choices];
    }

    public function secret(string $question, bool $fallback = true): array
    {
        return [$this->parseQuestion($question), null, InputOption::VALUE_OPTIONAL, '', null];
    }

    public function toCommandOptions(Command $command): void
    {
        $this->questions->each(function ($question) use ($command) {
            $command->addOption(...$question);
        });
    }

    public function toCommandCallback(Command $command): Closure
    {
        return function ($command) {
            return $this->questions->transform(function ($question) use ($command) {
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
    }

    protected function parseQuestion(string $question): string
    {
        if ($question === 'Email Address') {
            return 'email';
        }

        return Str::slug($question);
    }
}
