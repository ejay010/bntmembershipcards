<?php

use App\Mail\MembershipCardMail;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('guests are redirected from the members page', function () {
    $this->get(route('members.index'))->assertRedirect(route('login'));
});

test('authenticated users can visit the members page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('members.index'))->assertOk();
});

test('members can be enrolled via livewire component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::members.index')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('tier', 'Gold')
        ->set('status', 'active')
        ->set('expires_at', '2027-12-31')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('members', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'tier' => 'Gold',
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    $member = Member::where('email', 'john@example.com')->first();
    expect($member->membership_number)->toStartWith('BNT-'.date('Y').'-');
    expect($member->uuid)->not->toBeNull();
    expect($member->expires_at->format('Y-m-d'))->toEqual('2027-12-31');
});

test('member card emails can be triggered and sent', function () {
    Mail::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $member = Member::factory()->create();

    Livewire::test('pages::members.index')
        ->call('sendCardEmail', $member->id);

    Mail::assertQueued(MembershipCardMail::class, function ($mail) use ($member) {
        return $mail->hasTo($member->email) && $mail->member->id === $member->id;
    });

    $member->refresh();
    expect($member->email_sent_at)->not->toBeNull();
});

test('apple pass download endpoint downloads pass and updates download timestamp', function () {
    $member = Member::factory()->create([
        'apple_wallet_downloaded_at' => null,
    ]);

    $response = $this->get(route('members.pass.apple', $member->uuid));

    $response->assertHeader('Content-Type', 'application/vnd.apple.pkpass');
    $response->assertHeader('Content-Disposition', 'attachment; filename='.$member->membership_number.'.pkpass');

    $member->refresh();
    expect($member->apple_wallet_downloaded_at)->not->toBeNull();
});

test('google pass redirect endpoint redirects or loads mock page and updates timestamp', function () {
    $member = Member::factory()->create([
        'google_wallet_downloaded_at' => null,
    ]);

    $response = $this->get(route('members.pass.google', $member->uuid));

    $response->assertOk(); // Loads mock page view

    $member->refresh();
    expect($member->google_wallet_downloaded_at)->not->toBeNull();
});
