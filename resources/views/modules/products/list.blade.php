<div>@foreach($products as $p)<p>{{ $p->name }} - {{ $p->sku }}</p>@endforeach{{ $products->links() }}</div>
