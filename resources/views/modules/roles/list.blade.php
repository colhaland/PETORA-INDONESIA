<div>@foreach($roles as $r)<p>{{ $r->name }} ({{ $r->permissions->count() }} perms)</p>@endforeach{{ $roles->links() }}</div>
