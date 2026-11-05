<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Purchase Orders</h1>
        @can('purchasing.create')
            <a href="{{ route('purchasing.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-white">Buat PO</a>
        @endcan
    </div>
    @error('po') <p class="text-red-600">{{ $message }}</p> @enderror

    <div class="space-y-3">
        @forelse ($purchaseOrders as $po)
            <article class="rounded border p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $po->po_number }} · {{ $po->supplier?->name }}</h2>
                        <p>Status: {{ $po->status }} · Total: {{ number_format((float) $po->total, 2, ',', '.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if ($po->status === 'draft')
                            @can('purchasing.approve')
                                <button type="button" wire:click="approve({{ $po->id }})" class="rounded border px-3 py-2">Setujui</button>
                            @endcan
                        @endif
                        @if (in_array($po->status, ['approved', 'partially_received'], true))
                            @can('purchasing.receive')
                                <button type="button" wire:click="beginReceiving({{ $po->id }})" class="rounded border px-3 py-2">Terima barang</button>
                            @endcan
                        @endif
                    </div>
                </div>
                <ul class="mt-3 list-disc pl-5">
                    @foreach ($po->items as $item)
                        <li>{{ $item->product?->name ?? 'Produk dihapus' }} · {{ $item->quantity }} × {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</li>
                    @endforeach
                </ul>
            </article>
        @empty
            <p>Belum ada purchase order.</p>
        @endforelse
    </div>

    @if ($receivingPo)
        <section class="space-y-4 rounded border p-4">
            <h2 class="font-semibold">Penerimaan {{ $receivingPo->po_number }}</h2>
            @error('receiptLines') <p class="text-red-600">{{ $message }}</p> @enderror
            @foreach ($receiptLines as $index => $line)
                @php($poItem = $receivingPo->items->firstWhere('id', $line['purchase_order_item_id']))
                <div class="grid gap-3 md:grid-cols-5">
                    <p class="self-center">{{ $poItem?->product?->name }}</p>
                    <label>Jumlah diterima<input wire:model="receiptLines.{{ $index }}.quantity" type="number" min="0.001" step="0.001" class="mt-1 block w-full rounded border p-2"></label>
                    <label>Gudang
                        <select wire:model="receiptLines.{{ $index }}.warehouse_id" class="mt-1 block w-full rounded border p-2">
                            <option value="">Pilih gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Batch<input wire:model="receiptLines.{{ $index }}.batch_number" class="mt-1 block w-full rounded border p-2"></label>
                    <label>Kedaluwarsa<input wire:model="receiptLines.{{ $index }}.expiry_date" type="date" class="mt-1 block w-full rounded border p-2"></label>
                    <button type="button" wire:click="removeReceiptLine({{ $index }})" class="rounded border px-3 py-2">Lewati item</button>
                </div>
            @endforeach
            <div class="flex gap-2">
                <button type="button" wire:click="receive" class="rounded bg-emerald-600 px-4 py-2 text-white">Simpan penerimaan</button>
                <button type="button" wire:click="cancelReceiving" class="rounded border px-4 py-2">Batal</button>
            </div>
        </section>
    @endif
</div>
