<?php
declare(strict_types=1);
namespace App\Modules\Roles\Livewire;
use Spatie\Permission\Models\Role;
use Livewire\Component;
class RoleList extends Component
{
    public function render()
    {
        return view('modules.roles.list', ['roles' => Role::with('permissions')->paginate(20)])->layout('layouts.app');
    }
}
