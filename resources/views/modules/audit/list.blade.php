<div>@foreach($logs as $l)<p>{{ $l->action }} - {{ $l->created_at }}</p>@endforeach{{ $logs->links() }}</div>
