<?php

declare(strict_types=1);

use App\Modules\Auth\Livewire\Login;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/pharmacy', \App\Modules\Pharmacy\Livewire\PharmacyList::class)->name('pharmacy.index');
    Route::get('/finance', \App\Modules\Finance\Livewire\FinanceList::class)->name('finance.index');
    Route::get('/reports', \App\Modules\Reports\Livewire\ReportsIndex::class)->name('reports.index');


    Route::get('/booking', \App\Modules\Booking\Livewire\BookingList::class)->name('booking.index');
    Route::get('/veterinary', \App\Modules\Veterinary\Livewire\VisitList::class)->name('veterinary.index');

    Route::get('/grooming', \App\Modules\Grooming\Livewire\GroomingList::class)->name('grooming.index');
    Route::get('/purchasing', \App\Modules\Purchasing\Livewire\PurchaseOrderList::class)->name('purchasing.index');
    Route::get('/pos', \App\Modules\Pos\Livewire\PosPage::class)->name('pos.index');
    Route::get('/inventory', \App\Modules\Inventory\Livewire\StockList::class)->name('inventory.index');
    Route::get('/pets/create', \App\Modules\Pets\Livewire\PetForm::class)->name('pets.create');
    Route::get('/pets', \App\Modules\Pets\Livewire\PetList::class)->name('pets.index');
    Route::get('/products/create', \App\Modules\Products\Livewire\ProductForm::class)->name('products.create');
    Route::get('/products', \App\Modules\Products\Livewire\ProductList::class)->name('products.index');
    Route::get('/customers', \App\Modules\Customers\Livewire\CustomerList::class)->name('customers.index');
    Route::get('/customers/create', \App\Modules\Customers\Livewire\CustomerForm::class)->name('customers.create');
    Route::get('/', fn () => view('dashboard'))->name('dashboard');
});
