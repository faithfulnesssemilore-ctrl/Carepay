<?php

use App\Jobs\ExportStatementJob;
use App\Mail\StatementReady;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

it('queues a statement export request and sends an email when ready', function () {
    Mail::fake();
    Storage::fake('local');

    $user = User::factory()->create();

    Transaction::factory()->count(5)->create([
        'wallet_id' => $user->wallet->id,
        'user_id' => $user->id,
        'status' => 'completed',
        'type' => 'credit',
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($user)->postJson(route('statement.export'), [
        'start_date' => now()->subWeek()->toDateString(),
        'end_date' => now()->toDateString(),
    ]);

    $response->assertStatus(202)
        ->assertJson(['message' => 'Statement generation started. Check your email when the file is ready!']);

    dispatch(new ExportStatementJob(
        $user->id,
        now()->subWeek()->toDateString(),
        now()->toDateString(),
    ));

    Mail::assertQueued(StatementReady::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

it('returns validation error for invalid date range', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('statement.export'), [
        'start_date' => now()->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
    ]);

    $response->assertStatus(422);
});

it('returns 403 when a user tries to download another users statement', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $fileName = "{$otherUser->id}-20240101-20240131-test.xlsx";
    Storage::disk('local')->put("statements/{$fileName}", 'test');

    $response = $this->actingAs($user)->get(route('statement.download', ['file' => $fileName]));

    $response->assertStatus(403);
});

it('returns 404 when statement file does not exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('statement.download', ['file' => 'nonexistent.xlsx']));

    $response->assertStatus(404);
});
