<div>@foreach($pets as $p)<p>{{ $p->name }} ({{ $p->species }})</p>@endforeach{{ $pets->links() }}</div>
