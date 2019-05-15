<?php

namespace Reliqui\Ambulatory\Tests\Feature;

use Reliqui\Ambulatory\User;
use Reliqui\Ambulatory\Invitation;
use Illuminate\Support\Facades\Mail;
use Reliqui\Ambulatory\Tests\TestCase;
use Reliqui\Ambulatory\Mail\CredentialEmail;
use Reliqui\Ambulatory\Mail\InvitationEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
    public function only_admin_can_invite_users()
    {
        $this->signInAsDoctor();
        $this->postJson(route('ambulatory.invitations.store', 'new'), [])->assertStatus(403);

        $this->signInAsPatient();
        $this->postJson(route('ambulatory.invitations.store', 'new'), [])->assertStatus(403);

        Mail::assertNothingSent();
    }

    /** @test */
    public function admin_can_invite_user_via_email()
    {
        $this->signInAsAdmin();

        tap(factory(Invitation::class)->raw(), function ($attributes) {
            $this->postJson(route('ambulatory.invitations.store', 'new'), $attributes)
                ->assertOk()
                ->assertJson(['entry' => array_except($attributes, ['token'])]);
        });

        Mail::assertSent(InvitationEmail::class);
    }

    /** @test */
    public function admin_cannot_invite_user_when_email_is_already_use()
    {
        $this->signInAsAdmin();

        $user = factory(User::class)->create();

        tap(factory(Invitation::class)->raw(['email' => $user->email]), function ($attributes) {
            $this->postJson(route('ambulatory.invitations.store', 'new'), $attributes)
                ->assertStatus(422)
                ->assertJson([
                    'errors' => [
                        'email' => ["The email has already been taken."],
                    ]
                ]);
        });

        Mail::assertNothingSent();
    }

    /** @test */
    public function guest_cannot_confirm_their_invitation_with_invalid_token()
    {
        $this->get(route('ambulatory.accept.invitation', 'invalid-token'))->assertStatus(404);

        Mail::assertNotSent(CredentialEmail::class);
    }

    /** @test */
    public function guest_can_confirm_their_invitation_with_valid_token()
    {
        $invitation = factory(Invitation::class)->create();

        $this->get(route('ambulatory.accept.invitation', $invitation->token))
            ->assertRedirect(route('ambulatory.auth.login'))
            ->assertSessionHas('invitationAccepted', true);

        $this->assertDatabaseHas('ambulatory_users', ['email' => $invitation->email]);

        Mail::assertSent(CredentialEmail::class);
    }
}
