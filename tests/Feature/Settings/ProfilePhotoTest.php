<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\ProfilePhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_user_without_photo_sees_the_default_image_in_the_user_menu(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('intranet.dashboard'));

        $response->assertOk()->assertSee(asset('images/default-avatar.svg'));
    }

    public function test_user_can_upload_a_photo_and_sees_it_in_the_user_menu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ProfilePhoto::class)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->assertHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $path = $user->refresh()->profile_photo_path;

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->get(route('intranet.dashboard'))->assertSee(asset('storage/'.$path));
    }

    public function test_uploading_a_new_photo_deletes_the_previous_one(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ProfilePhoto::class)->set('photo', UploadedFile::fake()->image('primera.jpg'));

        $previousPath = $user->refresh()->profile_photo_path;

        Livewire::test(ProfilePhoto::class)->set('photo', UploadedFile::fake()->image('segunda.jpg'));

        Storage::disk('public')->assertMissing((string) $previousPath);
        Storage::disk('public')->assertExists((string) $user->refresh()->profile_photo_path);
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function rejectedFiles(): array
    {
        return [
            'not an image' => ['documento.pdf', 100],
            'svg image' => ['logo.svg', 100],
            'over 2 MB' => ['foto.jpg', 2049],
        ];
    }

    #[DataProvider('rejectedFiles')]
    public function test_invalid_files_are_rejected(string $fileName, int $kilobytes): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ProfilePhoto::class)
            ->set('photo', UploadedFile::fake()->create($fileName, $kilobytes))
            ->assertHasErrors('photo');

        $this->assertNull($user->refresh()->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
