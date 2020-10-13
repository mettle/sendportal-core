<?php

namespace Sendportal\Base\Http\Livewire;

use Exception;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Sendportal\Base\Setup\Admin;
use Sendportal\Base\Setup\Database;
use Sendportal\Base\Setup\Env;
use Sendportal\Base\Setup\Key;
use Sendportal\Base\Setup\Migrations;
use Sendportal\Base\Setup\StepInterface;
use Sendportal\Base\Setup\Url;

class Setup extends Component
{
    public $active = 0;

    public $steps = [
        ['name' => 'Environment File', 'completed' => false, 'handler' => Env::class, 'view' => Env::VIEW],
        ['name' => 'Application Key', 'completed' => false, 'handler' => Key::class, 'view' => Key::VIEW],
        ['name' => 'Application Url', 'completed' => false, 'handler' => Url::class, 'view' => Url::VIEW],
        ['name' => 'Database Connection', 'completed' => false, 'handler' => Database::class, 'view' => Database::VIEW],
        ['name' => 'Database Migrations', 'completed' => false, 'handler' => Migrations::class, 'view' => Migrations::VIEW],
        ['name' => 'Admin User Account', 'completed' => false, 'handler' => Admin::class, 'view' => Admin::VIEW],
    ];

    protected $listeners = [
        'next' => 'next'
    ];

    public function render()
    {
        return view('sendportal::livewire.setup');
    }

    public function mount(): void
    {
        $this->check();
    }

    public function previous(): void
    {
        $this->active--;
    }

    public function next(): void
    {
        $this->active++;

        $this->check();
    }

    public function getProgressProperty()
    {
        $completed = array_reduce($this->steps, function ($carry, $step) {
            return $carry + ($step['completed'] ? 1 : 0);
        }, 0);

        return (100 / count($this->steps)) * ($completed);
    }

    public function check(): bool
    {
        $handler = $this->getConcreteHandler();

        $completed = $handler->check();

        $this->steps[$this->active]['completed'] = $completed;

        if ($completed and $this->active < count($this->steps) - 1) {
            $this->next();
        }

        return $completed;
    }

    public function run(?array $data): void
    {
        $this->resetValidation();

        $handler = $this->getConcreteHandler();

        if (method_exists($handler, 'validate')) {
            try {
                $data = $handler->validate($data);
            } catch (ValidationException $e) {
                session()->flashInput($data);

                throw $e;
            }
        }

        try {
            $completed = $handler->run($data);

            $this->steps[$this->active]['completed'] = $completed;

            if ($completed and $this->active < count($this->steps) - 1) {
                $this->next();
            }
        } catch (Exception $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    /**
     * Get the concrete Step class.
     *
     * @return StepInterface
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getConcreteHandler(): StepInterface
    {
        $step = $this->steps[$this->active];

        return app()->make($step['handler']);
    }
}
