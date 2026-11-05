<div>@foreach($balances as $b)<p>{{ $b->product->name ?? "-" }} | {{ $b->warehouse->name ?? "-" }} | {{ $b->quantity }}</p>@endforeach{{ $balances->links() }}</div>
