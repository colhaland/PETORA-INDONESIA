<div>@foreach($users as $u)<p>{{ $u->name }} ({{ $u->email }})</p>@endforeach{{ $users->links() }}</div>
