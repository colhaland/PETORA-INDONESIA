<?php

declare(strict_types=1);

namespace App\Modules\SystemSettings\Livewire;

use App\Support\SettingsService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SettingsPage extends Component
{
    public string $key = '';

    public string $value = '';

    public function save(): void
    {
        Gate::authorize('settings.manage');
        SettingsService::set($this->key, $this->value, auth()->id());
        session()->flash('success', 'Pengaturan disimpan.');
    }

    public function render()
    {
        return view('modules.system-settings.page')->layout('layouts.app');
    }
}
