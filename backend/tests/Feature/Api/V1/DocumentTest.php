<?php

namespace Tests\Feature\Api\V1;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Establishment;
use App\Models\Logement;
use App\Models\User;
use App\Services\DocumentService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_establishment_manager_can_upload_a_document_for_their_own_logement(): void
    {
        $establishment = Establishment::factory()->create();
        $logement = Logement::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->post('/api/v1/documents', [
                'logement_id' => $logement->id,
                'type' => DocumentType::ASSIGNMENT_ORDER->value,
                'file' => UploadedFile::fake()->create('arrete.pdf', 100, 'application/pdf'),
            ])
            ->assertCreated()
            ->assertJsonPath('data.original_name', 'arrete.pdf')
            ->assertJsonMissingPath('data.file_path');

        $document = Document::first();
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_uploading_a_document_without_any_attachment_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->post('/api/v1/documents', [
                'type' => DocumentType::OTHER->value,
                'file' => UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['logement_id']);
    }

    public function test_a_disallowed_file_type_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $logement = Logement::factory()->create();

        $this->actingAsUser($admin)
            ->fromFrontend()
            ->post('/api/v1/documents', [
                'logement_id' => $logement->id,
                'type' => DocumentType::OTHER->value,
                'file' => UploadedFile::fake()->create('script.php', 10, 'application/x-httpd-php'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_establishment_manager_cannot_upload_a_document_for_another_establishments_logement(): void
    {
        $ownEstablishment = Establishment::factory()->create();
        $otherLogement = Logement::factory()->create();
        $manager = User::factory()->establishmentManager($ownEstablishment)->create();

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->post('/api/v1/documents', [
                'logement_id' => $otherLogement->id,
                'type' => DocumentType::OTHER->value,
                'file' => UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['logement_id']);
    }

    public function test_establishment_manager_cannot_delete_a_document(): void
    {
        $establishment = Establishment::factory()->create();
        $logement = Logement::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $document = app(DocumentService::class)->store(
            UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
            ['logement_id' => $logement->id, 'type' => DocumentType::OTHER->value],
            $manager,
        );

        $this->actingAsUser($manager)
            ->fromFrontend()
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertForbidden();
    }

    public function test_downloading_a_document_streams_the_original_file(): void
    {
        $establishment = Establishment::factory()->create();
        $logement = Logement::factory()->for($establishment)->create();
        $manager = User::factory()->establishmentManager($establishment)->create();

        $document = app(DocumentService::class)->store(
            UploadedFile::fake()->create('arrete.pdf', 50, 'application/pdf'),
            ['logement_id' => $logement->id, 'type' => DocumentType::OTHER->value],
            $manager,
        );

        $response = $this->actingAsUser($manager)
            ->fromFrontend()
            ->get("/api/v1/documents/{$document->id}/download")
            ->assertOk();

        $this->assertStringContainsString('arrete.pdf', $response->headers->get('content-disposition'));
    }
}
