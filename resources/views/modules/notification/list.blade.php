<div>@foreach($notifications as $n)<p>{{ $n->type }} - {{ $n->data }}</p>@endforeach{{ $notifications->links() }}</div>
