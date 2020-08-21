<?php

namespace Sendportal\Base\Setup;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

class Url implements StepInterface
{
    const VIEW = 'sendportal::setup.steps.url';

    public function check(): bool
    {
        if (config('app.url') !== 'http://localhost') {
            return true;
        }

        return false;
    }

    public function run(?array $input): bool
    {
        $this->writeToEnvironmentFile('APP_URL', $input['url']);

        config()->set('app.url', $input['url']);

        return true;
    }

    public function validate(array $input = []): array
    {
        $validationRules = [
            'url' => ['required', 'url']
        ];

        $validator = Validator::make($input, $validationRules);

        return $validator->validate();
    }

    protected function writeToEnvironmentFile(string $key, ?string $value): void
    {
        file_put_contents(app()->environmentFilePath(), preg_replace(
            "/^{$key}.*/m",
            "{$key}={$value}",
            file_get_contents(app()->environmentFilePath())
        ));

        if (!$this->checkEnvValuePresent($key, $value)) {
            throw new RuntimeException("Failed to persist environment variable value. {$key}={$value}");
        }
    }

    protected function checkEnvValuePresent(string $key, ?string $value): bool
    {
        $envContents = file_get_contents(app()->environmentFilePath());

        $needle = "{$key}={$value}";

        return Str::contains($envContents, $needle);
    }
}
