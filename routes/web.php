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
    Route::get('/accounting', \App\Modules\Accounting\Livewire\AccountingList::class)->name('accounting.index');
    Route::get('/home-grooming', \App\Modules\HomeGrooming\Livewire\HomeGroomingList::class)->name('home-grooming.index');
    Route::get('/transport', \App\Modules\Transport\Livewire\TransportList::class)->name('transport.index');
    Route::get('/hotel', \App\Modules\Hotel\Livewire\HotelList::class)->name('hotel.index');
    Route::get('/membership', \App\Modules\Membership\Livewire\MembershipList::class)->name('membership.index');
    Route::get('/attendance', \App\Modules\Attendance\Livewire\AttendanceList::class)->name('attendance.index');
    Route::get('/commission', \App\Modules\Commission\Livewire\CommissionList::class)->name('commission.index');
    Route::get('/promotion', \App\Modules\Promotion\Livewire\PromotionList::class)->name('promotion.index');
    Route::get('/loyalty', \App\Modules\Loyalty\Livewire\LoyaltyList::class)->name('loyalty.index');
    Route::get('/staff', \App\Modules\Staff\Livewire\StaffList::class)->name('staff.index');
    Route::get('/staff/create', \App\Modules\Staff\Livewire\StaffForm::class)->name('staff.create');
    Route::get('/staff/{id}', \App\Modules\Staff\Livewire\StaffDetail::class)->name('staff.show');
    Route::get('/notifications', \App\Modules\Notification\Livewire\NotificationList::class)->name('notifications.index');
    Route::get('/users', \App\Modules\Users\Livewire\UserList::class)->name('users.index');
    Route::get('/roles', \App\Modules\Roles\Livewire\RoleList::class)->name('roles.index');
    Route::get('/audit', \App\Modules\Audit\Livewire\AuditLogList::class)->name('audit.index');
    Route::get('/branches', \App\Modules\Branches\Livewire\BranchList::class)->name('branches.index');
    Route::get('/two-factor/setup', \App\Modules\Auth\Livewire\TwoFactorSetup::class)->name('two-factor.setup');
    Route::get('/settings', \App\Modules\SystemSettings\Livewire\SettingsPage::class)->name('settings.index');

    Route::get('/pharmacy/create', \App\Modules\Pharmacy\Livewire\PharmacyForm::class)->name('pharmacy.create');
    Route::get('/pharmacy/{id}', \App\Modules\Pharmacy\Livewire\PharmacyDetail::class)->name('pharmacy.show');
    Route::get('/pharmacy', \App\Modules\Pharmacy\Livewire\PharmacyList::class)->name('pharmacy.index');
    Route::get('/finance/create', \App\Modules\Finance\Livewire\FinanceForm::class)->name('finance.create');
    Route::get('/finance', \App\Modules\Finance\Livewire\FinanceList::class)->name('finance.index');
    Route::get('/reports', \App\Modules\Reports\Livewire\ReportsIndex::class)->name('reports.index');


    Route::get('/booking/create', \App\Modules\Booking\Livewire\BookingForm::class)->name('booking.create');
    Route::get('/booking/{id}', \App\Modules\Booking\Livewire\BookingDetail::class)->name('booking.show');
    Route::get('/booking', \App\Modules\Booking\Livewire\BookingList::class)->name('booking.index');
    Route::get('/veterinary/create', \App\Modules\Veterinary\Livewire\VisitForm::class)->name('veterinary.create');
    Route::get('/veterinary/{id}', \App\Modules\Veterinary\Livewire\VisitDetail::class)->name('veterinary.show');
    Route::get('/veterinary', \App\Modules\Veterinary\Livewire\VisitList::class)->name('veterinary.index');

    Route::get('/grooming/create', \App\Modules\Grooming\Livewire\GroomingForm::class)->name('grooming.create');
    Route::get('/grooming', \App\Modules\Grooming\Livewire\GroomingList::class)->name('grooming.index');
    Route::get('/purchasing/create', \App\Modules\Purchasing\Livewire\PurchaseOrderForm::class)->name('purchasing.create');
    Route::get('/purchasing', \App\Modules\Purchasing\Livewire\PurchaseOrderList::class)->name('purchasing.index');
    Route::get('/pos/create', \App\Modules\Pos\Livewire\PosForm::class)->name('pos.create');
    Route::get('/pos', \App\Modules\Pos\Livewire\PosPage::class)->name('pos.index');
    Route::get('/inventory', \App\Modules\Inventory\Livewire\StockList::class)->name('inventory.index');
    Route::get('/pets/create', \App\Modules\Pets\Livewire\PetForm::class)->name('pets.create');
    Route::get('/pets/create', \App\Modules\Pets\Livewire\PetForm::class)->name('pets.create');
    Route::get('/pets/{id}', \App\Modules\Pets\Livewire\PetDetail::class)->name('pets.show');
    Route::get('/pets', \App\Modules\Pets\Livewire\PetList::class)->name('pets.index');
    Route::get('/products/create', \App\Modules\Products\Livewire\ProductForm::class)->name('products.create');
    Route::get('/products/create', \App\Modules\Products\Livewire\ProductForm::class)->name('products.create');
    Route::get('/products/{id}', \App\Modules\Products\Livewire\ProductDetail::class)->name('products.show');
    Route::get('/products', \App\Modules\Products\Livewire\ProductList::class)->name('products.index');
    Route::get('/customers', \App\Modules\Customers\Livewire\CustomerList::class)->name('customers.index');
    Route::get('/customers/create', \App\Modules\Customers\Livewire\CustomerForm::class)->name('customers.create');
    Route::get('/', fn () => view('dashboard'))->name('dashboard');
    Route::get('/customers/{id}', \App\Modules\Customers\Livewire\CustomerDetail::class)->name('customers.show');
});
