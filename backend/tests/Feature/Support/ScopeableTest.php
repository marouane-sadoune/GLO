<?php

namespace Tests\Feature\Support;

use App\Models\Department;
use App\Models\Establishment;
use App\Models\Logement;
use App\Models\Occupation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_sees_every_logement(): void
    {
        Logement::factory()->count(3)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->assertSame(3, Logement::visibleTo($admin)->count());
    }

    public function test_department_admin_only_sees_logements_in_their_department(): void
    {
        $departmentA = Department::factory()->create();
        $establishmentA = Establishment::factory()->for($departmentA)->create();
        $establishmentB = Establishment::factory()->create();

        $logementA = Logement::factory()->for($establishmentA)->create();
        Logement::factory()->for($establishmentB)->create();

        $admin = User::factory()->departmentAdmin($departmentA)->create();

        $visible = Logement::visibleTo($admin)->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains($logementA));
    }

    public function test_establishment_manager_only_sees_logements_in_their_establishment(): void
    {
        $department = Department::factory()->create();
        $establishmentA = Establishment::factory()->for($department)->create();
        $establishmentB = Establishment::factory()->for($department)->create();

        $logementA = Logement::factory()->for($establishmentA)->create();
        Logement::factory()->for($establishmentB)->create();

        $manager = User::factory()->establishmentManager($establishmentA)->create();

        $visible = Logement::visibleTo($manager)->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains($logementA));
    }

    public function test_occupation_scoping_reaches_through_the_logement_relation(): void
    {
        $department = Department::factory()->create();
        $establishment = Establishment::factory()->for($department)->create();
        $otherEstablishment = Establishment::factory()->create();

        $logement = Logement::factory()->for($establishment)->create();
        $occupation = Occupation::factory()->for($logement)->create();
        Occupation::factory()->for(Logement::factory()->for($otherEstablishment))->create();

        $admin = User::factory()->departmentAdmin($department)->create();

        $visible = Occupation::visibleTo($admin)->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains($occupation));
    }

    public function test_establishment_scoping_uses_its_own_columns(): void
    {
        $departmentA = Department::factory()->create();
        $establishmentA = Establishment::factory()->for($departmentA)->create();
        Establishment::factory()->create();

        $admin = User::factory()->departmentAdmin($departmentA)->create();

        $visible = Establishment::visibleTo($admin)->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains($establishmentA));
    }

    public function test_user_scoping_by_department(): void
    {
        $departmentA = Department::factory()->create();
        $admin = User::factory()->departmentAdmin($departmentA)->create();
        $peer = User::factory()
            ->establishmentManager(Establishment::factory()->for($departmentA)->create())
            ->create();
        User::factory()->departmentAdmin()->create();

        $visible = User::visibleTo($admin)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains($admin));
        $this->assertTrue($visible->contains($peer));
    }
}
