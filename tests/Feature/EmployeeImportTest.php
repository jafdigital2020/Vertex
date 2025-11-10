<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Jobs\ImportEmployeesJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeImportTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $subscription;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.example.com',
            'database' => 'test_db',
            'status' => 'active'
        ]);

        // Create test plan
        $this->plan = Plan::create([
            'name' => 'Test Plan',
            'employee_limit' => 10,
            'price' => 1000,
            'billing_cycle' => 'monthly',
            'features' => ['basic_hr']
        ]);

        // Create subscription
        $this->subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'active_license' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth()
        ]);

        // Create test user
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'active_license' => true,
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);

        Storage::fake('local');
    }

    public function test_successful_import_within_limits()
    {
        // Create a CSV content with 5 employees (within the 10-employee limit)
        $csvContent = "First Name,Last Name,Username,Email,Password,Role\n";
        $csvContent .= "John,Doe,jdoe,john@example.com,password123,Employee\n";
        $csvContent .= "Jane,Smith,jsmith,jane@example.com,password123,Employee\n";
        $csvContent .= "Bob,Johnson,bjohnson,bob@example.com,password123,Employee\n";
        $csvContent .= "Alice,Brown,abrown,alice@example.com,password123,Employee\n";
        $csvContent .= "Charlie,Wilson,cwilson,charlie@example.com,password123,Employee\n";

        // Create a fake CSV file
        $file = UploadedFile::fake()->createWithContent('employees.csv', $csvContent);

        Queue::fake();

        // Test the import endpoint
        $response = $this->actingAs($this->user)
            ->post(route('import.employee.csv'), [
                'csv_file' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'details' => [
                    'rows_to_import' => 5,
                    'current_users' => 1,
                    'total_after_import' => 6,
                    'plan_limit' => 10
                ]
            ]);

        // Verify the job was dispatched
        Queue::assertPushed(ImportEmployeesJob::class);
    }

    public function test_import_blocked_when_exceeding_limits()
    {
        // Create a CSV content with 12 employees (exceeding the 10-employee limit)
        $csvContent = "First Name,Last Name,Username,Email,Password,Role\n";
        for ($i = 1; $i <= 12; $i++) {
            $csvContent .= "User{$i},Test{$i},user{$i},user{$i}@example.com,password123,Employee\n";
        }

        // Create a fake CSV file
        $file = UploadedFile::fake()->createWithContent('employees.csv', $csvContent);

        Queue::fake();

        // Test the import endpoint
        $response = $this->actingAs($this->user)
            ->post(route('import.employee.csv'), [
                'csv_file' => $file
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Import blocked due to license limits'
            ])
            ->assertJsonPath('errors.license_limit.details.current_users', 1)
            ->assertJsonPath('errors.license_limit.details.trying_to_import', 12)
            ->assertJsonPath('errors.license_limit.details.would_exceed_by', 3);

        // Verify no job was dispatched
        Queue::assertNotPushed(ImportEmployeesJob::class);
    }

    public function test_import_fails_with_no_subscription()
    {
        // Delete the subscription
        $this->subscription->delete();

        $csvContent = "First Name,Last Name,Username,Email,Password,Role\n";
        $csvContent .= "John,Doe,jdoe,john@example.com,password123,Employee\n";

        $file = UploadedFile::fake()->createWithContent('employees.csv', $csvContent);

        Queue::fake();

        $response = $this->actingAs($this->user)
            ->post(route('import.employee.csv'), [
                'csv_file' => $file
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'No active subscription found. Please contact support.'
            ]);

        Queue::assertNotPushed(ImportEmployeesJob::class);
    }
}
