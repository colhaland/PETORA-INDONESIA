<form wire:submit="save" class="space-y-6">
    <h1 class="text-xl font-semibold">Buat Purchase Order</h1>
    <label class="block">Supplier
        <select wire:model="supplierId" class="mt-1 block w-full rounded border p-2" required>
            <option value="0">Pilih supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
        </select>
    </label>
    @error('supplierId') <p class="text-red-600">{{ $message }}</p> @enderror

    <div class="space-y-3">
        <h2 class="font-medium">Item PO</h2>
        @foreach ($items as $index => $item)
            <div class="grid gap-3 md:grid-cols-[2fr_1fr_1fr_auto]">
                <label>Produk
                    <select wire:model="items.{{ $index }}.product_id" class="mt-1 block w-full rounded border p-2" required>
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}{{ $product->track_inventory ? ' (stok)' : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Jumlah dipesan<input wire:model="items.{{ $index }}.quantity" type="number" min="0.001" step="0.001" class="mt-1 block w-full rounded border p-2" required></label>
                <label>Harga modal/unit<input wire:model="items.{{ $index }}.unit_cost" type="number" min="0" step="0.01" class="mt-1 block w-full rounded border p-2" required></label>
                <button type="button" wire:click="removeItem({{ $index }})" class="self-end rounded border px-3 py-2">Hapus</button>
                @error("items.$index.product_id") <p class="text-red-600">{{ $message }}</p> @enderror
                @error("items.$index.quantity") <p class="text-red-600">{{ $message }}</p> @enderror
                @error("items.$index.unit_cost") <p class="text-red-600">{{ $message }}</p> @enderror
            </div>
        @endforeach
        @error('items') <p class="text-red-600">{{ $message }}</p> @enderror
        <button type="button" wire:click="addItem" class="rounded border px-3 py-2">Tambah item</button>
    </div>

    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Simpan PO</button>
</form>
