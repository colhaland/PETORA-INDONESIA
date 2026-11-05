<form wire:submit="save" class="space-y-4">
    <label class="block">Nama produk<input wire:model="name" class="mt-1 block w-full rounded border p-2" required></label>
    <label class="block">SKU<input wire:model="sku" class="mt-1 block w-full rounded border p-2" required></label>
    <label class="block">Harga jual<input wire:model="sellPrice" type="number" min="0" step="0.01" class="mt-1 block w-full rounded border p-2"></label>
    <label class="flex items-center gap-2"><input wire:model="trackInventory" type="checkbox">Lacak stok untuk produk ini</label>
    @error('name') <p class="text-red-600">{{ $message }}</p> @enderror
    @error('sku') <p class="text-red-600">{{ $message }}</p> @enderror
    @error('sellPrice') <p class="text-red-600">{{ $message }}</p> @enderror
    @error('trackInventory') <p class="text-red-600">{{ $message }}</p> @enderror
    <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Simpan</button>
</form>
