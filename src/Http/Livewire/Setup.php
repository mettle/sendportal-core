<?php

namespace Sendportal\Base\Http\Livewire;

use Exception;
use Livewire\Component;
use Sendportal\Base\Setup\Admin;
use Sendportal\Base\Setup\Database;
use Sendportal\Base\Setup\Env;
use Sendportal\Base\Setup\Key;
use Sendportal\Base\Setup\Migrations;
use Sendportal\Base\Setup\Url;

class Setup extends Component
{
    public $active = 0;

    public $steps = [
        'env' => ['name' => 'Check Environment', 'completed' => false, 'handler' => Env::class, 'view' => Env::VIEW],
        'key' => ['name' => 'Application Key', 'completed' => false, 'handler' => Key::class, 'view' => Key::VIEW],
        'url' => ['name' => 'Application Url', 'completed' => false, 'handler' => Url::class, 'view' => Url::VIEW],
        'db' => ['name' => 'Database Connection', 'completed' => false, 'handler' => Database::class, 'view' => Database::VIEW],
        'migrations' => ['name' => 'Database Migrations', 'completed' => false, 'handler' => Migrations::class, 'view' => Migrations::VIEW],
        'admin' => ['name' => 'Admin User Account', 'completed' => false, 'handler' => Admin::class, 'view' => Admin::VIEW],
    ];

    protected $listeners = [
        'next' => 'next'
    ];

    public function render()
    {
        return view('sendportal::livewire.setup');
    }

    public function mount()
    {
        $this->check();
    }

    public function previous()
    {
        $this->active--;
    }

    public function next()
    {
        $this->active++;

        $this->check();
    }

    public function getActiveKeyProperty()
    {
        return array_keys($this->steps)[$this->active];
    }

    public function getProgressProperty()
    {
        $completed = array_reduce($this->steps, function ($carry, $step) {
            return $carry + ($step['completed'] ? 1 : 0);
        }, 0);

        return (100 / count($this->steps)) * ($completed);
    }

    public function check()
    {
        $step = $this->steps[$this->getActiveKeyProperty()];

        $handler = app()->make($step['handler']);

        $completed = $handler->check();

        $this->steps[$this->getActiveKeyProperty()]['completed'] = $completed;

        if ($completed and $this->active < count($this->steps) - 1) {
            $this->next();
        }

        return $completed;
    }

    public function checkAgain()
    {
        $outcome = $this->check();

        if (!$outcome) {
            session()->flash('error', 'Not working. Please check your configuration and try again!');
        }
    }

    public function run(?array $data)
    {
        $this->resetValidation();

        $step = $this->steps[$this->activeKey];

        $handler = app()->make($step['handler']);

        if (method_exists($handler, 'validate')) {
            $data = $handler->validate($data);

            $this->resetErrorBag();
        }

        try {
            $return = $handler->run($data);

            $this->steps[$this->activeKey]['completed'] = $return;
        } catch (Exception $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }
}
