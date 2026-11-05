<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\MedicalVisit;
use App\Models\Pet;
use App\Models\User;
use App\Modules\Veterinary\Services\VeterinaryService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MedicalDataProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_ignores_client_doctor_id_and_rejects_other_branch(): void
    {
        $this->seed([PermissionSeeder::class, BranchSeeder::class]);
        $branch = Branch::query()->where('code', 'PST')->firstOrFail();
        $otherBranch = Branch::create(['name' => 'Cabang B', 'code' => 'B', 'timezone' => 'Asia/Jakarta']);
        $doctor = User::factory()->create();
        $doctor->givePermissionTo('veterinary.create');
        $doctor->branches()->sync([$branch->id]);
        $forgedDoctor = User::factory()->create();
        $customer = Customer::create(['name' => 'Pemilik', 'customer_number' => 'CUS-MED-1']);
        $pet = Pet::create([
            'pet_code' => 'PET-MED-1',
            'customer_id' => $customer->id,
            'name' => 'Mimi',
            'species' => 'cat',
        ]);
        $this->actingAs($doctor);

        $visit = app(VeterinaryService::class)->createVisit([
            'branch_id' => $branch->id,
            'pet_id' => $pet->id,
            'doctor_id' => $forgedDoctor->id,
            'subjective' => 'Pemeriksaan',
        ]);

        $this->assertSame($doctor->id, $visit->doctor_id);
        $this->expectException(HttpException::class);
        app(VeterinaryService::class)->createVisit([
            'branch_id' => $otherBranch->id,
            'pet_id' => $pet->id,
            'doctor_id' => $forgedDoctor->id,
        ]);
    }

    public function test_medical_revision_saves_valid_encrypted_json_snapshot(): void
    {
        $this->seed([PermissionSeeder::class, BranchSeeder::class]);
        $branch = Branch::query()->where('code', 'PST')->firstOrFail();
        $doctor = User::factory()->create();
        $doctor->givePermissionTo(
            'veterinary.create',
            'medical_records.finalize',
            'medical_records.update',
        );
        $doctor->branches()->sync([$branch->id]);
        $pet = Pet::create([
            'pet_code' => 'PET-REV-1',
            'customer_id' => Customer::create(['name' => 'Pemilik', 'customer_number' => 'CUS-REV-1'])->id,
            'name' => 'Mimi',
            'species' => 'cat',
        ]);
        $this->actingAs($doctor);
        $service = app(VeterinaryService::class);
        $visit = $service->createVisit([
            'branch_id' => $branch->id,
            'pet_id' => $pet->id,
            'subjective' => 'Sebelum revisi',
        ]);
        $service->finalize($visit);
        $service->revise($visit->fresh(), ['subjective' => 'Sesudah revisi', 'revision_reason' => 'Koreksi']);

        $snapshot = json_decode(DB::table('medical_record_revisions')->value('previous_snapshot'), true, flags: JSON_THROW_ON_ERROR);
        $previousValues = json_decode(Crypt::decryptString($snapshot['encrypted']), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Sebelum revisi', Crypt::decryptString($previousValues['subjective']));
        $this->assertSame('Sesudah revisi', $visit->fresh()->subjective);
    }

    public function test_database_foreign_keys_restrict_deletion_of_referenced_medical_data(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Deletion restrictions are asserted against PostgreSQL production constraints.');
        }

        $this->seed([PermissionSeeder::class, BranchSeeder::class]);
        $branch = Branch::query()->where('code', 'PST')->firstOrFail();
        $doctor = User::factory()->create();
        $customer = Customer::create(['name' => 'Pemilik', 'customer_number' => 'CUS-DEL-1']);
        $pet = Pet::create([
            'pet_code' => 'PET-DEL-1',
            'customer_id' => $customer->id,
            'name' => 'Mimi',
            'species' => 'cat',
        ]);
        $visit = MedicalVisit::create([
            'branch_id' => $branch->id,
            'pet_id' => $pet->id,
            'doctor_id' => $doctor->id,
            'status' => 'final',
            'finalized_at' => now(),
        ]);

        foreach ([
            fn () => DB::table('users')->where('id', $doctor->id)->delete(),
            fn () => DB::table('pets')->where('id', $pet->id)->delete(),
            fn () => DB::table('branches')->where('id', $branch->id)->delete(),
        ] as $deleteReferencedRecord) {
            try {
                DB::transaction($deleteReferencedRecord);
                $this->fail('A medical foreign key unexpectedly allowed a hard delete.');
            } catch (QueryException) {
                $this->assertDatabaseHas('medical_visits', ['id' => $visit->id]);
            }
        }
    }
}
