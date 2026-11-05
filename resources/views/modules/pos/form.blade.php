<div class="space-y-6">
    <h1 class="text-xl font-semibold">Point of Sale</h1>
    <p>Order {{ $order->order_number }} · Status: {{ $order->status }}</p>

    <section class="rounded border p-4">
        <label class="block">Gudang
            <select wire:model="warehouseId" wire:change="setWarehouse" class="mt-1 block w-full rounded border p-2">
                <option value="">Pilih gudang</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}{{ $warehouse->is_main ? ' (Utama)' : '' }}</option>
                @endforeach
            </select>
        </label>
        @error('warehouseId') <p class="text-red-600">{{ $message }}</p> @enderror
    </section>

    <form wire:submit="addItem" class="flex flex-wrap items-end gap-3">
        <label class="min-w-64 flex-1">Produk
            <select wire:model="productId" class="mt-1 block w-full rounded border p-2" required>
                <option value="0">Pilih produk</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }} · {{ number_format((float) $product->sell_price, 2, ',', '.') }}</option>
                @endforeach
            </select>
        </label>
        <label>Jumlah<input wire:model="quantity" type="number" min="0.001" step="0.001" class="mt-1 block rounded border p-2"></label>
        <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Tambah item</button>
        @error('productId') <p class="w-full text-red-600">{{ $message }}</p> @enderror
        @error('quantity') <p class="w-full text-red-600">{{ $message }}</p> @enderror
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead><tr><th>Produk</th><th>Jumlah</th><th>Harga</th><th>Total</th></tr></thead>
            <tbody>
                @forelse ($order->items as $item)
                    <tr class="border-t">
                        <td class="py-2">{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}</td>
                        <td>{{ number_format((float) $item->total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3">Belum ada item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-lg font-semibold">Total: {{ number_format((float) $order->total, 2, ',', '.') }}</p>
    <form wire:submit="pay" class="flex flex-wrap items-end gap-3">
        <label>Metode pembayaran
            <select wire:model="paymentMethod" class="mt-1 block rounded border p-2">
                <option value="cash">Tunai</option>
                <option value="card">Kartu</option>
                <option value="bank_transfer">Transfer bank</option>
                <option value="e_wallet">Dompet digital</option>
            </select>
        </label>
        <label>Nominal<input wire:model="paymentAmount" type="number" min="0.01" step="0.01" class="mt-1 block rounded border p-2"></label>
        <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-white">Bayar</button>
        @error('paymentMethod') <p class="w-full text-red-600">{{ $message }}</p> @enderror
        @error('paymentAmount') <p class="w-full text-red-600">{{ $message }}</p> @enderror
    </form>
</div>
