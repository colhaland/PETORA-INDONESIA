<div>@foreach($branches as $b)<p>{{ $b->name }} ({{ $b->code }})</p>@endforeach{{ $branches->links() }}</div>
