    <?php

    use App\Models\Project;
    use App\Models\User;
    use App\Models\Document;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Support\Facades\Storage;

    uses(RefreshDatabase::class);

    beforeEach(function () {
    Storage::fake('local');

    $this->seed();
    });

    # Test case 1 : ALlows a user to view a project in their tenant
    it('allows a user to view a project in their tenant', function () {
    
        #Create a mock user using factory
        # database => factories -> UserFactory
        $user = User::factory()->create();

        # Create a mock project using factory and set the organization id to user's organization id
        # database => factories -> ProjectFactory

        # Factory normally will use Faker to create fake project, description etc
    $project = Project::factory()->create([
        'organization_id' => $user->organization_id,
    ]);

    // Step to run the test case
    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk();   // Expect 200
    });

    # Test case 2 - Block cross access tenant

    it('blocks cross-tenant project access', function () {
    $alice = User::factory()->create([
        'organization_id' => 1,    // org id = 1
    ]);

    $betaProject = Project::factory()->create([
        'organization_id' => 2,
        'name' => 'Confidential Beta Project',
    ]);

    $this->actingAs($alice)
        ->get(route('projects.show', $betaProject)) // get, post, delete, put
        ->assertNotFound() // Expect 404 , assertStatus(403) , assertNotFound()
        ->assertDontSee('Confidential Beta Project'); // Expect Not See the "COnfidential Beta Project"

        // https://laravel.com/framework/docs/testing
    });

    # Test case 3 - An Organization A administrator can update an Organization A project.
    // it('allows  an admin to update a project in their tenant', function () {

    // });


    // # Test case 4 - An Organization A administrator cannot update an Organization B project.
    // it('blocks cross-tenant project update', function () {

    // });

    // # Test case 5 - An Organization A administrator cannot delete an Organization B project.
    // it('blocks cross-tenant project delete', function () {

    // });

    # Test case 6 - An Organization A member cannot delete an Organization A project.
    it('blocks project delete for non admin in same tenant', function () {

    /// User from tenant A

    $alice = User::factory()->create([
        'organization_id' => 1,    // org id = 1
    ]);

    // Project from tenant A

    $alphaProject = Project::factory()->create([
        'organization_id' => 1,
        'name' => 'Confidential Alpha Project',
    ]);
    $this->actingAs($alice)->delete('projects.destroy',["project"=>$alphaProject])->assertNotFound();


    });

    # Test case 7 - A user cannot access a document belonging to another tenant.
    it('blocks project view for user in same tenant', function () {

    /// User from tenant A

    $alice = User::factory()->create([
        'organization_id' => 1,    // org id = 1
    ]);

    // Project from tenant B

    $betaProject = Project::factory()->create([
        'organization_id' => 2,
        'name' => 'Confidential Beta Project',
    ]);


    /// Document from tenant B

    $betaDocument = Document::factory()->create([
        'project_id' =>$betaProject->id, 
        'original_name'=>'beta_document.txt'
    ]);

    $this->actingAs($alice)->get(route('projects.documents.download',
    ['project'=> $betaProject->id, 'document'=> $betaDocument->id]))->assertNotFound();

    });


