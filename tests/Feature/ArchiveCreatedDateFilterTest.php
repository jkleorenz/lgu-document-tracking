<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ArchiveCreatedDateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_by_created_date_inclusive()
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'secret123',
            'status' => 'verified',
        ]);
        $this->actingAs($user);

        $dept = Department::create([
            'name' => 'Testing Dept',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $d1 = Document::create([
            'document_number' => 'DOC-001',
            'title' => 'Doc Jan 01',
            'document_type' => 'Memo',
            'created_by' => $user->id,
            'department_id' => $dept->id,
            'status' => 'Archived',
            'archived_at' => Carbon::parse('2025-03-01'),
        ]);
        $d1->created_at = Carbon::parse('2025-01-01');
        $d1->save();

        $d2 = Document::create([
            'document_number' => 'DOC-002',
            'title' => 'Doc Jan 15',
            'document_type' => 'Memo',
            'created_by' => $user->id,
            'department_id' => $dept->id,
            'status' => 'Completed',
            'archived_at' => Carbon::parse('2025-03-01'),
        ]);
        $d2->created_at = Carbon::parse('2025-01-15');
        $d2->save();

        $d3 = Document::create([
            'document_number' => 'DOC-003',
            'title' => 'Doc Feb 01',
            'document_type' => 'Memo',
            'created_by' => $user->id,
            'department_id' => $dept->id,
            'status' => 'Archived',
            'archived_at' => Carbon::parse('2025-03-01'),
        ]);
        $d3->created_at = Carbon::parse('2025-02-01');
        $d3->save();

        $response = $this->get(route('archive.index', [
            'from_date' => '2025-01-10',
            'to_date' => '2025-01-31',
        ]));
        $response->assertStatus(200);
        $response->assertSee('DOC-002');
        $response->assertDontSee('DOC-001');
        $response->assertDontSee('DOC-003');

        $response2 = $this->get(route('archive.index', [
            'from_date' => '2025-01-01',
            'to_date' => '2025-01-15',
        ]));
        $response2->assertStatus(200);
        $response2->assertSee('DOC-001');
        $response2->assertSee('DOC-002');
        $response2->assertDontSee('DOC-003');
    }

    public function test_rejects_invalid_date_range()
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester2@example.com',
            'password' => 'secret123',
            'status' => 'verified',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('archive.index', [
            'from_date' => '2025-02-01',
            'to_date' => '2025-01-01',
        ]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['to_date']);
    }
}

