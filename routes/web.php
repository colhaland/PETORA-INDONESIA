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

Route::middleware(['auth', 'ensure.2fa'])->group(function () {
    Route::get('/integration', \App\Modules\Integration\Livewire\IntegrationList::class)->name('integration.index')->middleware('can:integrations.view');
    Route::get('/customer-portal', \App\Modules\CustomerPortal\Livewire\CustomerPortalList::class)->name('customer-portal.index')->middleware('can:portal.view');
    Route::get('/consignment', \App\Modules\Consignment\Livewire\ConsignmentList::class)->name('consignment.index')->middleware('can:consignment.view');
    Route::get('/accounting', \App\Modules\Accounting\Livewire\AccountingList::class)->name('accounting.index')->middleware('can:accounting.view');
    Route::get('/home-grooming', \App\Modules\HomeGrooming\Livewire\HomeGroomingList::class)->name('home-grooming.index')->middleware('can:home_grooming.view');
    Route::get('/transport', \App\Modules\Transport\Livewire\TransportList::class)->name('transport.index')->middleware('can:transport.view');
    Route::get('/hotel', \App\Modules\Hotel\Livewire\HotelList::class)->name('hotel.index')->middleware('can:hotel.view');
    Route::get('/membership', \App\Modules\Membership\Livewire\MembershipList::class)->name('membership.index')->middleware('can:membership.view');
    Route::get('/attendance', \App\Modules\Attendance\Livewire\AttendanceList::class)->name('attendance.index')->middleware('can:attendance.view');
    Route::get('/commission', \App\Modules\Commission\Livewire\CommissionList::class)->name('commission.index')->middleware('can:commission.view');
    Route::get('/promotion', \App\Modules\Promotion\Livewire\PromotionList::class)->name('promotion.index')->middleware('can:promotions.view');
    Route::get('/loyalty', \App\Modules\Loyalty\Livewire\LoyaltyList::class)->name('loyalty.index')->middleware('can:loyalty.view');
    Route::get('/staff', \App\Modules\Staff\Livewire\StaffList::class)->name('staff.index')->middleware('can:staff.view');
    Route::get('/staff/create', \App\Modules\Staff\Livewire\StaffForm::class)->name('staff.create')->middleware('can:staff.create');
    Route::get('/staff/{id}', \App\Modules\Staff\Livewire\StaffDetail::class)->name('staff.show')->middleware('can:staff.view');
    Route::get('/notifications', \App\Modules\Notification\Livewire\NotificationList::class)->name('notifications.index')->middleware('can:notifications.view');

    Route::get('/users', \App\Modules\Users\Livewire\UserList::class)->name('users.index')->middleware('can:users.view');
    Route::get('/roles', \App\Modules\Roles\Livewire\RoleList::class)->name('roles.index')->middleware('can:roles.view');
    Route::get('/audit', \App\Modules\Audit\Livewire\AuditLogList::class)->name('audit.index')->middleware('can:audit.view');
    Route::get('/branches', \App\Modules\Branches\Livewire\BranchList::class)->name('branches.index')->middleware('can:branches.view');
    Route::get('/two-factor/setup', \App\Modules\Auth\Livewire\TwoFactorSetup::class)->name('two-factor.setup');
    Route::get('/settings', \App\Modules\SystemSettings\Livewire\SettingsPage::class)->name('settings.index')->middleware('can:settings.view');

    Route::get('/pharmacy/create', \App\Modules\Pharmacy\Livewire\PharmacyForm::class)->name('pharmacy.create')->middleware('can:pharmacy.manage');
    Route::get('/pharmacy/{id}', \App\Modules\Pharmacy\Livewire\PharmacyDetail::class)->name('pharmacy.show')->middleware('can:pharmacy.view');
    Route::get('/pharmacy', \App\Modules\Pharmacy\Livewire\PharmacyList::class)->name('pharmacy.index')->middleware('can:pharmacy.view');
    Route::get('/finance/create', \App\Modules\Finance\Livewire\FinanceForm::class)->name('finance.create')->middleware('can:finance.expense_create');
    Route::get('/finance', \App\Modules\Finance\Livewire\FinanceList::class)->name('finance.index')->middleware('can:finance.view');
    Route::get('/reports', \App\Modules\Reports\Livewire\ReportsIndex::class)->name('reports.index')->middleware('can:reports.view');


    Route::get('/booking/create', \App\Modules\Booking\Livewire\BookingForm::class)->name('booking.create')->middleware('can:bookings.create');
    Route::get('/booking/{id}', \App\Modules\Booking\Livewire\BookingDetail::class)->name('booking.show')->middleware('can:bookings.view');
    Route::get('/booking', \App\Modules\Booking\Livewire\BookingList::class)->name('booking.index')->middleware('can:bookings.view');
    Route::get('/veterinary/create', \App\Modules\Veterinary\Livewire\VisitForm::class)->name('veterinary.create')->middleware('can:veterinary.create');
    Route::get('/veterinary/{id}', \App\Modules\Veterinary\Livewire\VisitDetail::class)->name('veterinary.show')->middleware('can:veterinary.view');
    Route::get('/veterinary', \App\Modules\Veterinary\Livewire\VisitList::class)->name('veterinary.index')->middleware('can:veterinary.view');

    Route::get('/grooming/create', \App\Modules\Grooming\Livewire\GroomingForm::class)->name('grooming.create')->middleware('can:grooming.create');
    Route::get('/grooming', \App\Modules\Grooming\Livewire\GroomingList::class)->name('grooming.index')->middleware('can:grooming.view');
    Route::get('/purchasing/create', \App\Modules\Purchasing\Livewire\PurchaseOrderForm::class)->name('purchasing.create')->middleware('can:purchasing.create');
    Route::get('/purchasing', \App\Modules\Purchasing\Livewire\PurchaseOrderList::class)->name('purchasing.index')->middleware('can:purchasing.view');
    Route::get('/pos/create', \App\Modules\Pos\Livewire\PosForm::class)->name('pos.create')->middleware('can:pos.create');
    Route::get('/pos', \App\Modules\Pos\Livewire\PosPage::class)->name('pos.index')->middleware('can:pos.view');
    Route::get('/inventory', \App\Modules\Inventory\Livewire\StockList::class)->name('inventory.index')->middleware('can:inventory.view');
    Route::get('/pets/create', \App\Modules\Pets\Livewire\PetForm::class)->name('pets.create')->middleware('can:pets.create');
    Route::get('/pets/{id}', \App\Modules\Pets\Livewire\PetDetail::class)->name('pets.show')->middleware('can:pets.view');
    Route::get('/pets', \App\Modules\Pets\Livewire\PetList::class)->name('pets.index')->middleware('can:pets.view');
    Route::get('/products/create', \App\Modules\Products\Livewire\ProductForm::class)->name('products.create')->middleware('can:products.create');
    Route::get('/products/{id}', \App\Modules\Products\Livewire\ProductDetail::class)->name('products.show')->middleware('can:products.view');
    Route::get('/products', \App\Modules\Products\Livewire\ProductList::class)->name('products.index')->middleware('can:products.view');
    Route::get('/customers', \App\Modules\Customers\Livewire\CustomerList::class)->name('customers.index')->middleware('can:customers.view');
    Route::get('/customers/create', \App\Modules\Customers\Livewire\CustomerForm::class)->name('customers.create')->middleware('can:customers.create');
    Route::get('/', fn () => view('dashboard'))->name('dashboard')->middleware('can:dashboard.view');
    Route::get('/customers/{id}', \App\Modules\Customers\Livewire\CustomerDetail::class)->name('customers.show')->middleware('can:customers.view');
});
