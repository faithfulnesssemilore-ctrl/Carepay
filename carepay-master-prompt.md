# CarePay — Master Copilot Agent Prompt
# Paste into GitHub Copilot Chat → Agent Mode (Ctrl+Shift+I → switch to Agent)
# One section at a time. Wait for Copilot to finish before the next.
# Keys and credentials are asked at the very end — do not skip ahead.

---

## SECTION 0 — Project Brain (Paste this every single session, always first)

```
You are working on CarePay — a Nigerian fintech web app.

Stack:
- Laravel 12, PHP 8.2+
- Livewire 3 + Volt (full-stack reactive, no separate API layer)
- Laravel Fortify (auth) + Sanctum (tokens) + Horizon (queues)
- Bootstrap 5 + custom dark purple glassmorphism CSS theme
- Lucide icons via x-lucide-{name} blade components
- Font Awesome is loaded but use Lucide for ALL new icons
- Paystack for payments, virtual accounts, and webhooks
- ichtrojan/laravel-otp for OTP flows

Custom CSS classes (never remove these):
  card-luxury, btn-gradient, gradient-text, gradient-bg-primary,
  text-muted-custom, text-primary-custom, bg-secondary-custom,
  shadow-primary, shadow-primary-lg, hover-lift, glass-effect,
  blur-circle-primary, blur-circle-accent, icon-container,
  icon-container-sm, icon-container-md, rounded-xl, stat-card

Blade UI components (use these for all new UI):
  x-ui.card, x-ui.button, x-ui.input, x-ui.textarea, x-ui.badge, x-ui.alert

Colors:
  Background: #0a0a0f, #141420, #1a1a24
  Purple accent: #a855f7, #c084fc
  Green (credit): #22c55e
  Red (debit/error): #ef4444

Services and where they live:
  WalletService       → app/Services/walletServices.php   (class WalletService)
  TransferService     → app/Services/TransferService.php
  VirtualAccountService → app/Services/VirtualAccountService.php
  PinService          → app/Services/PinService.php
  PaymentService      → app/Services/PaymentServices.php  (class PaymentService)
  BillPaymentService  → app/Services/BillPaymentService.php (create if missing)

Critical data rules — never break these:
  - Balance stored in KOBO. Store: amount * 100. Display: balance / 100.
  - Transaction type values in DB: 'credit' and 'debit' — never 'in' or 'out'
  - Wallet table name: 'wallet' (singular, matches migration)
  - All Transaction and Wallet amounts are in KOBO
  - Phone and id_number are encrypted — never query them directly
  - Recipient lookup must use username or email (not phone)
  - PINs are hashed with bcrypt — verify with Hash::check(), never plain compare

Hard rules for Copilot:
  - Read every file before editing it — no exceptions
  - Never delete existing logic, methods, or properties
  - Never switch CSS frameworks or remove existing CSS classes
  - Use Lucide for every new icon
  - Comments: write like a 16-year-old — casual English, no emojis, no fancy words
  - No PHPDoc blocks, no @param, no @return, no markdown documentation files
  - No inline comments longer than one short line
  - If you need a key, credential, or env value — stop and ask me before writing code
  - When something is unclear — ask, do not guess
```

---

## SECTION 1 — Critical Bug Fixes (Do this before anything else)

```
Fix every bug listed here. Read each file before touching it.
Fix them in order — do not skip any.

BUG 1 — app/Models/User.php: fatal class structure error
The class body closes with } too early. The methods sendEmailVerificationNotification(),
hasVerifiedEmail(), and markEmailAsVerified() are written OUTSIDE the class body.
This is a fatal PHP parse error — the app cannot boot at all.

Fix: restructure so there is exactly ONE closing } at the very bottom of the file.
All methods go inside. Keep every line of logic exactly as-is.
While in the file also confirm the casts array has:
  'pin'       => 'hashed',
  'password'  => 'hashed',
  'phone'     => Encrypted::class,
  'id_number' => Encrypted::class,
And confirm the class uses the SoftDeletes trait.

BUG 2 — routes/web.php: duplicate routes
/dashboard is registered twice. One is outside auth middleware (returns plain view).
Remove that one. Keep the one inside the auth+active middleware group.
/payment/callback is registered twice at the bottom. Remove the second copy.
The /test route exposes raw User::first() data to anyone. Remove it entirely.

BUG 3 — routes/web.php: GET logout is a security hole
Logout must be POST. Change the logout route to POST.
Search every blade file for href links to /logout and replace with:
  <form method="POST" action="/logout">
    @csrf
    <button type="submit" class="...existing classes...">Logout</button>
  </form>
Keep whatever styling the existing logout button had.

BUG 4 — bootstrap/app.php: middleware aliases not registered
The routes use ->middleware('role:admin') and ->middleware('active') but they are
never registered. Register both in the withMiddleware() section of bootstrap/app.php:
  'active' => App\Http\Middleware\EnsureAccountIsActive::class
  'role'   => App\Http\Middleware\RoleMiddleware::class

Then open app/Http/Middleware/RoleMiddleware.php.
If it does not exist, create it. Logic:
  - accept a $role parameter from the route
  - role values: 0=user, 1=admin, 2=super_admin
  - ->middleware('role:admin') → allow if $user->role >= 1
  - ->middleware('role:super_admin') → allow if $user->role >= 2
  - if denied → redirect to /dashboard with Session::flash('error', 'Access denied.')

BUG 5 — PaystackWebhookController: method name mismatch
routes/web.php calls the action as 'handleEvent' but the controller method is handle().
Rename the method to handleEvent() in the controller. Keep all logic inside it.

BUG 6 — TransferService: wrong field name for transaction type
TransferService.php creates Transaction records with key 'transaction_type' but the
Transaction model $fillable and WalletService both use 'type'.
Open the transactions migration to confirm the real column name.
Update TransferService to use the same key as the migration — use 'type'.

BUG 7 — DashboardPage: wrong transaction type filter values
DashboardPage.php filters transactions by 'in' and 'out' — these values do not exist.
The real values stored by WalletService are 'credit' and 'debit'.
Find every place in DashboardPage.php that uses 'in' or 'out' and fix them:
  'in'  → 'credit'
  'out' → 'debit'

BUG 8 — amount-step.blade.php: hardcoded balance
The blade view shows ₦12,450.00 hardcoded. Fix:
  In SendMoney.php add: public $walletBalance = 0;
  In mount(): $this->walletBalance = Auth::user()->wallet->balance / 100;
  In amount-step.blade.php replace the hardcoded amount with:
    ₦{{ number_format($walletBalance, 2) }}

BUG 9 — public/hot leftover file
Delete public/hot if it exists. Add public/hot to .gitignore.
This file makes Vite load assets from localhost:5173 in production and breaks everything.

BUG 10 — WalletService::credit() not used in webhook
Open PaystackWebhookController.php. If it uses $wallet->increment('balance', $amount)
directly, replace it with (new WalletService())->credit() so duplicate webhook calls
cannot double-credit a wallet. WalletService handles idempotency via the reference check.

After all 10 fixes run:
  php artisan route:list --compact
Show me the full output. Point out anything that looks wrong.
Then run: php artisan about
Show me that output too.
```

---

## SECTION 2 — Database: Migrations and Indexes

```
Read all migration files in database/migrations/ before doing anything.

TASK 1 — Confirm all columns exist
Open these migrations and confirm these columns are present:
  transactions: id, user_id, recipient_id, type, amount, reference, description,
                status, metadata, created_at, updated_at
  wallet: id, user_id, balance, status, created_at, updated_at
  users: id, first_name, last_name, email, username, phone, pin, role, status,
         kyc_verified, id_document, kyc_rejection_reason, created_at, updated_at, deleted_at
  virtual_accounts: id, user_id, account_number, account_name, bank_name, provider,
                    created_at, updated_at
  user_limits: id, user_id, single_transaction_limit, daily_transfer_limit,
               limit_reset_date, created_at, updated_at

If any column is missing, create a migration to add it:
  php artisan make:migration add_missing_columns_to_{table}_table

Missing column rules:
  - kyc_rejection_reason: string, nullable, on users table
  - recipient_id: foreignId, nullable, on transactions table
  - description: string, nullable, on transactions table
  - metadata: json, nullable, on transactions table

TASK 2 — Performance indexes
Create one new migration:
  php artisan make:migration add_performance_indexes

Add these indexes:
  transactions: composite index on (user_id, created_at)
  transactions: unique index on (reference) — prevents duplicate crediting
  transactions: index on (status)
  wallet: index on (user_id)
  users: confirm email is unique, confirm username is unique

TASK 3 — Run and confirm
  php artisan migrate
Show me the output. If anything fails, read the error and fix it.
```

---

## SECTION 3 — Models and Relationships

```
Read every model file before editing. Only add what is missing.

TASK 1 — User model relationships
Open app/Models/User.php. Confirm these relationships exist. Add any that are missing:
  public function wallet()       { return $this->hasOne(Wallet::class); }
  public function transactions() { return $this->hasMany(Transaction::class); }
  public function virtualAccount() { return $this->hasOne(VirtualAccount::class); }
  public function limits()       { return $this->hasOne(UserLimit::class); }
  public function sentTransactions() {
      return $this->hasMany(Transaction::class, 'user_id')->where('type', 'debit');
  }
  public function receivedTransactions() {
      return $this->hasMany(Transaction::class, 'recipient_id')->where('type', 'credit');
  }

TASK 2 — Transaction model
Open app/Models/Transaction.php. Confirm:
  - $fillable has: user_id, recipient_id, type, amount, reference, description, status, metadata
  - relationship: public function user() { return $this->belongsTo(User::class); }
  - relationship: public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
  - $casts has: 'metadata' => 'array', 'amount' => 'integer'

TASK 3 — Wallet model
Open app/Models/Wallet.php. Confirm:
  - protected $table = 'wallet'; (singular — matches migration)
  - $fillable has: user_id, balance, status
  - $casts has: 'balance' => 'integer'
  - relationship: public function user() { return $this->belongsTo(User::class); }

TASK 4 — UserLimit model
Open app/Models/UserLimit.php. Confirm:
  - $fillable has: user_id, single_transaction_limit, daily_transfer_limit, limit_reset_date
  - relationship: public function user() { return $this->belongsTo(User::class); }
  - Add a helper: public function dailyLimitInKobo() { return $this->daily_transfer_limit * 100; }
  - Add a helper: public function singleLimitInKobo() { return $this->single_transaction_limit * 100; }

TASK 5 — VirtualAccount model
Open app/Models/VirtualAccount.php. Confirm:
  - $fillable has: user_id, account_number, account_name, bank_name, provider
  - relationship: public function user() { return $this->belongsTo(User::class); }
```

---

## SECTION 4 — WalletService: The Core of All Money Movement

```
This is the most important service in the app. Read it carefully before touching anything.
Open app/Services/walletServices.php.

TASK 1 — credit() method
The credit() method should:
  1. check if a transaction with this reference already exists — if yes, return early (idempotent)
  2. use DB::transaction() with pessimistic locking:
       $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();
  3. increment the balance: $wallet->increment('balance', $amountInKobo)
  4. create a Transaction record:
       Transaction::create([
           'user_id'     => $userId,
           'type'        => 'credit',
           'amount'      => $amountInKobo,
           'reference'   => $reference,
           'description' => $description,
           'status'      => 'success',
       ])
  5. return the transaction record

If credit() already does all this correctly, leave it alone.
If it is missing idempotency (reference check), add it.
If it does not use lockForUpdate(), add it.

TASK 2 — debit() method
The debit() method should:
  1. use DB::transaction() with pessimistic locking
  2. check balance >= $amountInKobo — throw exception if not enough
  3. decrement balance: $wallet->decrement('balance', $amountInKobo)
  4. create Transaction record with type='debit'
  5. return the transaction record

TASK 3 — transfer() method
The transfer() method should:
  1. load sender limits from UserLimit::where('user_id', $senderId)->first()
  2. if no limits record, create defaults:
       UserLimit::create([
           'user_id' => $senderId,
           'single_transaction_limit' => 100000,  // NGN not kobo for limits
           'daily_transfer_limit'    => 500000,
           'limit_reset_date'        => today(),
       ])
  3. check single transaction limit: if $amountInKobo > $limits->singleLimitInKobo() → throw
  4. check daily limit — sum today's debits for this user:
       $todaySpent = Transaction::where('user_id', $senderId)
           ->where('type', 'debit')
           ->whereDate('created_at', today())
           ->sum('amount');
     if ($todaySpent + $amountInKobo) > $limits->dailyLimitInKobo() → throw with remaining amount
  5. use DB::transaction() to:
       a. debit sender
       b. credit recipient
       c. create debit transaction with recipient_id filled
       d. create credit transaction with user_id = recipientId
  6. return ['reference' => $reference, 'amount' => $amountInKobo]

TASK 4 — Wire SendMoney.php to use WalletService::transfer()
Open app/Livewire/SendMoney.php.
If it still calls TransferService::transfer(), change it to use WalletService::transfer().
WalletService has locking, idempotency, and limit enforcement built in.
TransferService can stay in the codebase for any other use but should not be used for
wallet-to-wallet transfers anymore.
```

---

## SECTION 5 — Send Money: Full OPay-Style Flow

```
Read ALL of these files before writing a single line:
  app/Livewire/SendMoney.php
  resources/views/livewire/send-money.blade.php
  resources/views/livewire/steps/recipient-step.blade.php
  resources/views/livewire/steps/amount-step.blade.php
  resources/views/livewire/steps/confirm-step.blade.php
  resources/views/livewire/steps/success-step.blade.php

TASK 1 — Simplify the steps
The current flow has a "method" step (wallet/bank/card) between amount and confirm.
For internal CarePay-to-CarePay transfers the method is always wallet — remove this step.
New flow: recipient → amount → confirm → success
Update the $steps array and all setStep() calls in SendMoney.php.
Update the progress indicator in send-money.blade.php to show 4 steps only.

TASK 2 — Real recipient search
Phone is encrypted so we cannot query it. We search by username.
Replace whatever recipient search exists with this:

  public $searchQuery  = '';
  public $searchResult = null;
  public $searchError  = '';

  public function searchRecipient()
  {
      $this->searchError  = '';
      $this->searchResult = null;

      if (strlen(trim($this->searchQuery)) < 2) {
          $this->searchError = 'Type at least 2 characters';
          return;
      }

      $found = User::where(function ($q) {
              $q->where('username', $this->searchQuery)
                ->orWhere('email', $this->searchQuery);
          })
          ->where('id', '!=', Auth::id())
          ->where('status', 'active')
          ->first(['id', 'first_name', 'last_name', 'username']);

      if (!$found) {
          $this->searchError = 'No CarePay user found. Check the username — it looks like @john_doe';
          return;
      }

      $this->searchResult = [
          'id'       => $found->id,
          'name'     => $found->first_name . ' ' . $found->last_name,
          'username' => '@' . $found->username,
          'initials' => strtoupper(substr($found->first_name, 0, 1) . substr($found->last_name, 0, 1)),
      ];
  }

  public function confirmRecipient()
  {
      if ($this->searchResult) {
          $this->selectedRecipient = $this->searchResult;
          $this->setStep('amount');
      }
  }

TASK 3 — Real recent contacts
Replace any hardcoded $recentContacts array with:

  public function loadRecentContacts()
  {
      $this->recentContacts = Transaction::where('user_id', Auth::id())
          ->where('type', 'debit')
          ->whereNotNull('recipient_id')
          ->with('recipient:id,first_name,last_name,username')
          ->latest()
          ->get()
          ->unique('recipient_id')
          ->take(6)
          ->map(function ($tx) {
              $r = $tx->recipient;
              if (!$r) return null;
              return [
                  'id'       => $r->id,
                  'name'     => $r->first_name . ' ' . $r->last_name,
                  'username' => '@' . $r->username,
                  'initials' => strtoupper(substr($r->first_name, 0, 1) . substr($r->last_name, 0, 1)),
              ];
          })
          ->filter()
          ->values()
          ->toArray();
  }

TASK 4 — Amount step: show limits
In SendMoney.php mount():
  public $walletBalance   = 0;
  public $todaySpentKobo  = 0;
  public $limits          = null;

  $user = Auth::user();
  $this->walletBalance  = $user->wallet->balance / 100;
  $this->limits         = $user->limits;
  $this->todaySpentKobo = Transaction::where('user_id', $user->id)
      ->where('type', 'debit')
      ->whereDate('created_at', today())
      ->sum('amount');

In amount-step.blade.php show below the balance:
  - Available: ₦{{ number_format($walletBalance, 2) }}
  - Daily limit: ₦{{ number_format(optional($limits)->daily_transfer_limit ?? 500000, 2) }}
  - Used today: ₦{{ number_format($todaySpentKobo / 100, 2) }}
  - Remaining: ₦{{ number_format(((optional($limits)->daily_transfer_limit ?? 500000) * 100 - $todaySpentKobo) / 100, 2) }}

TASK 5 — PIN modal on confirm step
In confirm-step.blade.php, the "Confirm & Send" button should open a PIN modal instead
of calling handleConfirm() directly.

In SendMoney.php:
  public $pin       = '';
  public $pinError  = '';

  public function openPinModal()
  {
      $this->pin = '';
      $this->pinError = '';
      $this->dispatch('open-pin-modal');
  }

  public function verifyAndSend()
  {
      $this->pinError = '';
      $user = Auth::user();

      if (!$user->pin) {
          $this->pinError = 'You have not set a transaction PIN yet. Go to Settings to create one.';
          return;
      }

      if (!Hash::check($this->pin, $user->pin)) {
          $this->pinError = 'Wrong PIN. Try again.';
          return;
      }

      $this->processTransfer();
  }

  private function processTransfer()
  {
      $this->isProcessing = true;
      try {
          $result = (new WalletService())->transfer(
              senderId:     Auth::id(),
              recipientId:  $this->selectedRecipient['id'],
              amountInKobo: (int) round(floatval($this->amount) * 100),
              description:  $this->note ?: 'Transfer to ' . $this->selectedRecipient['name']
          );
          $this->transferReference = $result['reference'];
          $this->dispatch('close-pin-modal');
          $this->setStep('success');
          $this->dispatch('toast', type: 'success', message: 'Transfer successful!');
      } catch (\Exception $e) {
          $this->pinError = $e->getMessage();
          $this->dispatch('toast', type: 'error', message: $e->getMessage());
      } finally {
          $this->isProcessing = false;
      }
  }

Add a Bootstrap modal with id="pinModal" to confirm-step.blade.php:
  - title "Enter your 4-digit PIN"
  - a single input: type="password" maxlength="4" pattern="[0-9]{4}" wire:model="pin"
    styled large, centered, letter-spacing wide
  - "Confirm Transfer" button: wire:click="verifyAndSend" wire:loading.attr="disabled"
  - error message area: @if($pinError) <p class="text-danger small">{{ $pinError }}</p> @endif

Add to @push('scripts') in send-money.blade.php:
  document.addEventListener('livewire:init', () => {
      Livewire.on('open-pin-modal', () => new bootstrap.Modal(document.getElementById('pinModal')).show());
      Livewire.on('close-pin-modal', () => bootstrap.Modal.getInstance(document.getElementById('pinModal'))?.hide());
  });

TASK 6 — Success step
In success-step.blade.php show:
  - big x-lucide-check-circle icon in green
  - "Money Sent!" heading
  - amount and recipient name
  - reference number: {{ $transferReference }}
  - "Send Again" button: wire:click="resetForm"
  - "Go to Dashboard" button: href="{{ route('dashboard') }}"

TASK 7 — Recipient step UI (OPay style)
Rewrite recipient-step.blade.php to look like OPay's transfer screen:

Top: search bar
  <div class="input-group mb-3">
    <span class="input-group-text" style="background:#1a1a24; border-color:rgba(168,85,247,0.3);">
      <x-lucide-search class="text-muted-custom" style="width:16px;height:16px;" />
    </span>
    <input type="text" wire:model.live.debounce.400ms="searchQuery"
           class="form-control" style="background:#1a1a24; border-color:rgba(168,85,247,0.3); color:white;"
           placeholder="Enter @username or email" />
    <button wire:click="searchRecipient" class="btn btn-gradient px-3">Find</button>
  </div>

If $searchError: small red text below the input.

If $searchResult: a result card with:
  - purple avatar circle (initials)
  - name bold + @username muted
  - x-lucide-check-circle in green on the right
  - "Send to this person" btn-gradient full width button: wire:click="confirmRecipient"

Recent section heading "Recent" with small muted text.
Recent contacts as a horizontal scroll row of circles:
  Each circle: 44px, gradient-bg-primary, initials text, name underneath (10px, truncate 8 chars)
  wire:click="selectContact({{ $contact['id'] }})"

Replace every Font Awesome icon in this file with Lucide.
```

---

## SECTION 6 — Add Money: Virtual Account + All Deposit Methods

```
Read these files before starting:
  app/Livewire/AddMoney.php
  resources/views/livewire/add-money.blade.php
  resources/views/livewire/steps/deposit-bank-transfer.blade.php
  resources/views/livewire/steps/deposit-card.blade.php
  resources/views/livewire/steps/deposit-ussd.blade.php
  resources/views/livewire/steps/deposit-cash.blade.php

TASK 1 — AddMoney.php: wire virtual account data
In mount():
  $va = Auth::user()->virtualAccount;
  $this->accountNumber    = $va?->account_number ?? '';
  $this->accountName      = $va?->account_name   ?? '';
  $this->bankName         = $va?->bank_name       ?? '';
  $this->hasVirtualAccount = $va !== null;

TASK 2 — Auto-create virtual account on registration
Open app/Actions/Fortify/CreateNewUser.php.
After the user is created, dispatch:
  \App\Jobs\CreateVirtualAccountJob::dispatch($user);

Create the job with: php artisan make:job CreateVirtualAccountJob
In handle():
  if (VirtualAccount::where('user_id', $this->user->id)->exists()) return;
  try {
      (new VirtualAccountService())->create($this->user);
  } catch (\Exception $e) {
      Log::error('Virtual account creation failed: ' . $e->getMessage(), ['user_id' => $this->user->id]);
  }
The job should implement ShouldQueue.

Ask me: "Do you want to backfill virtual accounts for existing users who don't have one?
Say yes and I will create a one-time artisan command you can run. Say no to skip."
Wait for my answer before creating the command.

TASK 3 — Bank transfer step: replace hardcoded data
Open deposit-bank-transfer.blade.php.
Replace every hardcoded value:
  "CarePay Virtual Bank" → {{ $bankName }}
  "7845621039"           → {{ $accountNumber }}
  "John Doe - CarePay"   → {{ $accountName }}

If $hasVirtualAccount is false, show instead:
  <div class="card-luxury text-center p-4">
    <x-lucide-clock class="text-primary-custom mb-3" style="width:40px;height:40px;" />
    <h6 class="fw-bold">Setting up your account</h6>
    <p class="text-muted-custom small">Your bank account is being created. Usually takes under a minute.</p>
    <button wire:click="$refresh" class="btn btn-gradient mt-2">Refresh</button>
  </div>

The copy buttons must copy the real dynamic values not hardcoded strings.
Replace all Font Awesome icons in this file with Lucide.

TASK 4 — Card deposit: wire to Paystack
Before writing this task, ask me:
  "Card deposits: do you want Paystack Redirect (user goes to Paystack page)
   or Paystack Inline (popup on your page)? Say redirect or inline."
Wait for my answer.

After I answer:
In AddMoney.php, when method = 'card' and user confirms:
  1. Validate cardAmount >= 100 (minimum ₦100)
  2. Generate reference: 'DEP_' . strtoupper(Str::random(16))
  3. Store in deposits table: user_id, amount (kobo), reference, status='pending'
  4. Call (new PaymentService())->initialize(email, amount * 100, reference, route('payment.callback'))
  5. Redirect to authorization_url if redirect mode, or emit event for inline popup

TASK 5 — USSD step
Open deposit-ussd.blade.php.
Add a bank selector: wire:model.live="selectedBank"
Banks and their USSD codes (use $accountNumber as the destination):
  GTBank:     *737*50*{amount}*{accountNumber}#
  Access:     *901*{amount}*{accountNumber}#
  First Bank: *894*{amount}*{accountNumber}#
  Zenith:     *966*{amount}*{accountNumber}#
  UBA:        *919*{amount}*{accountNumber}#
  GTBank numeric account: show account number to transfer to

In AddMoney.php add:
  public $selectedBank = '';
  public $ussdCode     = '';

  public function updatedSelectedBank($bank)
  {
      $codes = [
          'gtbank'     => '*737*50*' . $this->ussdAmount . '*' . $this->accountNumber . '#',
          'access'     => '*901*' . $this->ussdAmount . '*' . $this->accountNumber . '#',
          'firstbank'  => '*894*' . $this->ussdAmount . '*' . $this->accountNumber . '#',
          'zenith'     => '*966*' . $this->ussdAmount . '*' . $this->accountNumber . '#',
          'uba'        => '*919*' . $this->ussdAmount . '*' . $this->accountNumber . '#',
      ];
      $this->ussdCode = $codes[$bank] ?? '';
  }

Show the generated code large in a code block.
Add a "Open Dialer" button: <a href="tel:{{ urlencode($ussdCode) }}" class="btn btn-gradient w-100">Open Dialer</a>

TASK 6 — Method selection screen
In add-money.blade.php rewrite the method selection step:
  2x2 grid of method cards (col-6 each), each is card-luxury hover-lift
  
  Bank Transfer:
    icon: x-lucide-building-2 in icon-container-md gradient-bg-primary
    title: "Bank Transfer"
    subtitle: "From any Nigerian bank"
    badge: "Free" green rounded-pill
  
  Debit Card:
    icon: x-lucide-credit-card
    title: "Debit Card"
    subtitle: "Instant with your card"
    badge: "1.5% fee" yellow
  
  USSD:
    icon: x-lucide-smartphone
    title: "USSD"
    subtitle: "Dial from any phone"
    badge: "Free" green
  
  Cash:
    icon: x-lucide-banknote
    title: "Cash Deposit"
    subtitle: "Pay at an agent"
    badge: "Agent fee may apply" gray

At the top show current balance card:
  x-lucide-wallet + "Current Balance" + ₦{{ number_format(Auth::user()->wallet->balance / 100, 2) }}
```

---

## SECTION 7 — Dashboard: Intensive Improvement

```
Read these files before starting:
  app/Livewire/DashboardPage.php
  resources/views/livewire/dashboard-page.blade.php

TASK 1 — Fix all data loading in DashboardPage.php
Fix the transaction type filter bug first:
  Every place with 'in' → change to 'credit'
  Every place with 'out' → change to 'debit'

Fix balance display — divide by 100 for display:
  $this->balance = $wallet->balance / 100;
  $this->monthlyIncome   = Transaction::...->sum('amount') / 100;
  $this->monthlyExpenses = Transaction::...->sum('amount') / 100;

Add daily limit progress:
  $todaySpent = Transaction::where('user_id', $userId)->where('type','debit')
      ->whereDate('created_at', today())->sum('amount');
  $dailyLimitKobo = (optional(Auth::user()->limits)->daily_transfer_limit ?? 500000) * 100;
  $this->dailyLimitUsedPercent = $dailyLimitKobo > 0
      ? min(100, (int) round(($todaySpent / $dailyLimitKobo) * 100))
      : 0;

Add chart data:
  $chartMonths = $chartIncome = $chartExpense = [];
  for ($i = 5; $i >= 0; $i--) {
      $m = now()->subMonths($i);
      $chartMonths[]  = $m->format('M');
      $chartIncome[]  = round(Transaction::where('user_id', $userId)->where('type','credit')
          ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)
          ->sum('amount') / 100, 2);
      $chartExpense[] = round(Transaction::where('user_id', $userId)->where('type','debit')
          ->whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)
          ->sum('amount') / 100, 2);
  }
  $this->chartData = json_encode(['labels'=>$chartMonths,'income'=>$chartIncome,'expenses'=>$chartExpense]);

Use eager loading — no N+1 queries:
  $user = Auth::user()->load('wallet', 'limits', 'transactions');

TASK 2 — dashboard-page.blade.php: balance card
Replace the balance display with:
  - x-lucide-wallet icon + "Available Balance" label
  - Large ₦{{ number_format($balance, 2) }} amount
  - Daily limit progress bar below:
    <div class="d-flex justify-content-between small opacity-75 mb-1">
      <span>Daily limit</span><span>{{ $dailyLimitUsedPercent }}% used</span>
    </div>
    <div class="progress" style="height:3px; background:rgba(255,255,255,0.15);">
      <div class="progress-bar" style="width:{{ $dailyLimitUsedPercent }}%; background:#a855f7;"></div>
    </div>
  - Four quick action buttons in a row: Send, Add, Bills, History
    Each is a small card-luxury column with a Lucide icon and label

TASK 3 — Real Chart.js chart
Load Chart.js in app.blade.php head:
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

Replace the placeholder chart div with:
  <div style="position:relative; height:220px;">
    <canvas id="spendingChart"></canvas>
  </div>

  @push('scripts')
  <script>
  // set up the spending chart when the page loads
  function initSpendingChart() {
      const el = document.getElementById('spendingChart');
      if (!el) return;
      if (el._chart) el._chart.destroy();
      const data = @json($chartData);
      el._chart = new Chart(el.getContext('2d'), {
          type: 'line',
          data: {
              labels: data.labels,
              datasets: [
                  {
                      label: 'Income',
                      data: data.income,
                      borderColor: '#a855f7',
                      backgroundColor: 'rgba(168,85,247,0.1)',
                      borderWidth: 2,
                      pointBackgroundColor: '#a855f7',
                      pointRadius: 3,
                      fill: true,
                      tension: 0.4,
                  },
                  {
                      label: 'Expenses',
                      data: data.expenses,
                      borderColor: '#c084fc',
                      backgroundColor: 'rgba(192,132,252,0.05)',
                      borderWidth: 2,
                      pointBackgroundColor: '#c084fc',
                      pointRadius: 3,
                      fill: true,
                      tension: 0.4,
                  }
              ]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { labels: { color: '#888', font: { size: 11 } } } },
              scales: {
                  x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#888' } },
                  y: {
                      grid: { color: 'rgba(255,255,255,0.04)' },
                      ticks: { color: '#888', callback: v => '₦' + v.toLocaleString() }
                  }
              }
          }
      });
  }
  document.addEventListener('DOMContentLoaded', initSpendingChart);
  document.addEventListener('livewire:navigated', initSpendingChart);
  </script>
  @endpush

TASK 4 — Stat cards
Four stat cards in a row (col-6 col-md-3):
  Income card:     x-lucide-arrow-down-circle in green, amount, label "Money In"
  Expenses card:   x-lucide-arrow-up-circle   in red,   amount, label "Money Out"
  Transactions:    x-lucide-repeat            in purple, count, label "Transactions"
  Bills:           x-lucide-receipt           in purple, count, label "Bills Paid"
Each uses stat-card class + card-luxury.

TASK 5 — Recent transactions list
For each transaction in $recentTransactions:
  Left icon: x-lucide-arrow-down-left (credit, green bg) or x-lucide-arrow-up-right (debit, purple bg)
  Center: $tx->description ?: 'Transfer' in fw-semibold, date in small text-muted-custom
  Right: +₦X or -₦X in fw-bold (green for credit, white/muted for debit)
  Amounts: $tx->amount / 100 formatted with number_format

If no transactions: x-lucide-inbox large icon + "No transactions yet" + links to add-money and send-money.

TASK 6 — Replace all Font Awesome icons in dashboard blade
Search for <i class="fas or <i class="far and replace everything with Lucide.
Use the icon map from the final wiring check section.
```

---

## SECTION 8 — App Layout: Mobile Nav + Toast System

```
Read resources/views/components/layouts/app.blade.php before starting.
Rewrite it completely while keeping all layout logic intact.

The layout must have:

1. Top navbar (desktop only, d-none d-md-flex):
   Left: CarePay logo (icon-container-sm gradient-bg-primary + x-lucide-wallet + "CarePay" gradient-text)
   Right: x-lucide-bell button, user avatar circle (initials), POST logout form

2. Top mobile header (mobile only, d-flex d-md-none, sticky-top):
   Left: CarePay logo smaller
   Right: bell icon, avatar circle
   background: rgba(10,10,15,0.9), backdrop-filter:blur(10px)

3. Main content wrapper:
   <main class="pb-5 pb-md-3">
     <div class="container-fluid px-3 px-md-4" style="max-width:1200px; margin:0 auto;">
       {{ $slot }}
     </div>
   </main>

4. Bottom nav (mobile only, fixed-bottom, d-flex d-md-none):
   5 items: Dashboard, Send, Add, History, Profile
   Each is flex-fill with Lucide icon (20px) + label (9px) centered
   Active: color #a855f7 fw-semibold
   Inactive: color #666
   background: rgba(10,10,15,0.95), backdrop-filter:blur(20px)
   border-top: 1px solid rgba(168,85,247,0.15)
   Detect active route: request()->routeIs('dashboard'), etc.

5. Toast container (fixed top-right, z-index 9999):
   <div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;
        display:flex;flex-direction:column;gap:8px;max-width:300px;"></div>

6. Chart.js script tag in <head>:
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

7. @stack('scripts') just before </body>

8. Toast system script (inline before </body>):
   document.addEventListener('livewire:init', () => {
       Livewire.on('toast', (e) => {
           const colors = {success:'#22c55e',error:'#ef4444',info:'#a855f7',warning:'#f59e0b'};
           const el = document.createElement('div');
           el.style.cssText = 'background:#1a1a24;border:1px solid '+colors[e.type]+
               ';border-radius:10px;padding:12px 16px;color:white;font-size:13px;'+
               'box-shadow:0 8px 24px rgba(0,0,0,0.4);animation:slideIn 0.2s ease;'+
               'display:flex;align-items:center;gap:10px;';
           el.textContent = e.message;
           document.getElementById('toast-container').appendChild(el);
           setTimeout(() => { el.style.opacity='0'; el.style.transition='opacity 0.3s';
               setTimeout(()=>el.remove(),300); }, 4000);
       });
   });

   Add keyframe: @keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
```

---

## SECTION 9 — Bill Payment: VTPass Integration

```
Read these files before starting:
  app/Livewire/BillPayment.php
  resources/views/livewire/bill-payment.blade.php

Before writing any code, ask me:
  "Do you have a VTPass API key? Say yes and give me the key, or say no and I will mock it for now."
Wait for my answer.

TASK 1 — BillPaymentService
Create app/Services/BillPaymentService.php if it does not exist.

If VTPass key provided, implement real API calls:
  base URL: https://sandbox.vtpass.com/api (test) or https://api.vtpass.com/api (live)
  auth: Basic auth with username + password from .env as VTPASS_EMAIL and VTPASS_PASSWORD
  Or API key auth via 'api-key' header from .env as VTPASS_API_KEY

  Methods:
    buyAirtime($phone, $network, $amount) — POST /pay with serviceID=mtn/airtel/glo/9mobile
    buyData($phone, $network, $planCode) — POST /pay with serviceID={network}-data
    payElectricity($meterNumber, $disco, $amount, $meterType) — POST /pay with serviceID
    payCable($smartcard, $provider, $variation) — POST /pay with serviceID=dstv/gotv/startimes

  All methods:
    1. debit wallet first with WalletService::debit()
    2. call VTPass API
    3. if API call fails → refund wallet with WalletService::credit() with same reference
    4. create Transaction record with type='debit', description matching the bill type
    5. return response or throw exception

If no VTPass key, build a realistic mock:
  Validate inputs (Nigerian phone: 11 digits starting with 0, meter number: 11-13 digits)
  Deduct wallet
  Create transaction record
  Return fake token/confirmation — e.g. 'TOKEN-' . strtoupper(Str::random(8))
  Add comment: // this is mocked - replace with real VTPass call when you have the key

TASK 2 — BillPayment Livewire flow
Rewrite BillPayment.php to have these steps:
  category → provider → details → confirm → pin → success

  $step = 'category'
  $category = '' (airtime/data/electricity/cable)
  $provider = ''
  $amount = ''
  $phone = ''
  $meterNumber = ''
  $meterType = 'prepaid'
  $smartcard = ''
  $pin = ''
  $pinError = ''
  $reference = ''
  $confirmationToken = ''

  selectCategory($cat) — sets category, moves to provider step
  selectProvider($prov) — sets provider, moves to details step
  submitDetails() — validates inputs based on category, moves to confirm step
  openPinModal() — dispatches open-pin-modal event
  verifyAndPay() — checks PIN with Hash::check, calls BillPaymentService, moves to success

TASK 3 — bill-payment.blade.php
Rewrite with same multi-step look as SendMoney (progress indicator + step cards):

Category step: 4 icon cards in 2x2 grid
  Airtime:     x-lucide-phone
  Data:        x-lucide-wifi
  Electricity: x-lucide-zap
  Cable TV:    x-lucide-tv-2

Provider step: button group with provider names
  Airtime/Data providers: MTN, Airtel, Glo, 9mobile (as badge-style toggle buttons)
  Electricity: EKEDC, IKEDC, AEDC, Ibadan, Kaduna, Enugu, JED, PHEDC, BEDC
  Cable: DSTV, GOtv, Startimes

Details step: different form per category
  Airtime: phone input + amount input
  Data: phone input + data plan selector (fetch plans or show common ones)
  Electricity: meter number + type toggle (prepaid/postpaid) + amount
  Cable: smartcard number + bouquet selector

Confirm step: show summary of what will be paid
  Provider, amount, recipient (phone/meter/smartcard)
  Wallet deduction preview
  "Pay Now" button → openPinModal()

PIN modal: same pattern as SendMoney

Success step:
  x-lucide-check-circle large green
  "Payment Successful!"
  Show confirmation token if electricity
  "Pay Again" and "Dashboard" buttons

Replace all Font Awesome icons with Lucide throughout.
```

---

## SECTION 10 — Admin Panel

```
Read every admin file before touching any of them:
  app/Livewire/Admin/Admin.php
  app/Livewire/Admin/AdminUsers.php
  app/Livewire/Admin/AdminTransactions.php
  app/Livewire/Admin/AdminKYC.php
  All blade views in resources/views/livewire/admin/

TASK 1 — Admin dashboard stats
In Admin.php add to loadData() or mount():
  $this->totalUsers       = User::count();
  $this->activeUsers      = User::where('status', 'active')->count();
  $this->pendingKyc       = User::where('kyc_verified', false)->whereNotNull('id_document')->count();
  $this->totalVolume      = Transaction::where('type', 'debit')->where('status', 'success')->sum('amount') / 100;
  $this->todayVolume      = Transaction::where('type', 'debit')->where('status', 'success')
      ->whereDate('created_at', today())->sum('amount') / 100;
  $this->todayCount       = Transaction::whereDate('created_at', today())->count();

Show these as stat-card items in the admin dashboard blade view.

TASK 2 — AdminUsers.php: full user management
Ensure:
  - search by name, email, or username with a text input wire:model.live.debounce.400ms="search"
  - results paginated 15 per page
  - each row shows: name, email, role badge, wallet balance, kyc status, account status
  - suspendUser($id): sets $user->status = 'suspended'
  - activateUser($id): sets $user->status = 'active'
  - changeRole($id, $role): sets $user->role = $role (only super_admin can see this button)
  - wallet balance: $user->wallet->balance / 100 formatted

TASK 3 — AdminTransactions.php: full transaction oversight
Ensure:
  - filter by type (all/credit/debit), status (all/success/pending/failed), date range
  - search by reference number
  - paginated 20 per page
  - each row: user name, type badge, amount (/ 100), reference, date, status badge
  - CSV export button: wire:click="exportCsv"
  - exportCsv() method: use response()->streamDownload() to download all filtered transactions as CSV

TASK 4 — AdminKYC.php: approve and reject
Ensure:
  - shows users with id_document not null
  - approveKyc($userId): sets kyc_verified = true, sends notification to user
  - rejectKyc($userId, $reason): sets kyc_verified = false, stores $reason in kyc_rejection_reason
  - the blade view shows id_document as a clickable link if it is a file path
  - Approve button: wire:click="approveKyc({{ $user->id }})" class btn-gradient
  - Reject: modal with a reason textarea, then wire:click="rejectKyc({{ $user->id }}, $reason)"

TASK 5 — Admin layout sidebar
Read resources/views/components/layouts/admin.blade.php.
Ensure sidebar has:
  - CarePay logo at top
  - Nav links: Dashboard, Users, Transactions, KYC — active state uses route()->current()
  - Super admin only: Settings link
  - POST logout form at the bottom
  - Mobile: collapsible offcanvas sidebar using Bootstrap
```

---

## SECTION 11 — Security

```
Read each file before editing. These are security-critical changes.

TASK 1 — PIN hashing (if not done)
Open app/Services/PinService.php.
If it stores PIN as plain text, fix:
  Store: $user->update(['pin' => Hash::make($pin)])
  Verify: Hash::check($inputPin, $user->pin)
If it already uses Hash::make and Hash::check, leave it alone.

TASK 2 — Rate limiting
Open routes/web.php. Add throttle middleware to sensitive routes:
  login route:             ->middleware('throttle:5,1')
  register route:          ->middleware('throttle:3,1')
  /webhook/paystack route: ->middleware('throttle:120,1')

TASK 3 — Paystack webhook IP validation
Open PaystackWebhookController.php. After the signature check, add:
  $allowedIps = ['52.31.139.75', '52.49.173.169', '52.214.14.220'];
  $requestIp  = $request->ip();
  if (!in_array($requestIp, $allowedIps)) {
      Log::warning('Webhook from unknown IP: ' . $requestIp);
      // still process it but log the warning — do not reject, Paystack IPs can change
  }

TASK 4 — Remove the test route
routes/web.php has a Route::get('/test', ...) that returns User::first().
Delete it entirely.

TASK 5 — CSRF on all forms
Search all blade files for <form> tags that do not have @csrf inside them.
Add @csrf to every form that does not have it.
```

---

## SECTION 12 — Feature Tests

```
Create feature tests in tests/Feature/. Use RefreshDatabase on all of them.
Run: php artisan make:test [TestName] for each one below.

Tests to create:

GuestCanViewHomePage:
  GET / → assertStatus(200)

GuestIsRedirectedFromDashboard:
  GET /dashboard → assertRedirect('/login')

UserCanLogin:
  create User with factory, actingAs, GET /dashboard → assertStatus(200)

UserCannotAccessAdminPanel:
  create regular user (role=0), actingAs, GET /admin/dashboard → assertRedirect

AdminCanAccessAdminPanel:
  create admin user (role=1), actingAs, GET /admin/dashboard → assertStatus(200)

WalletCreditWorksCorrectly:
  create user + wallet with 0 balance
  call (new WalletService())->credit(userId, 10000, 'ref-abc', 'Test credit')
  assertDatabaseHas('wallet', ['user_id'=>$user->id, 'balance'=>10000])
  assertDatabaseHas('transactions', ['reference'=>'ref-abc', 'type'=>'credit', 'amount'=>10000])

WalletDebitFailsIfInsufficientBalance:
  create user + wallet with 5000 kobo balance
  expect exception: (new WalletService())->debit(userId, 10000, 'ref-xyz', 'Test debit')
  assertDatabaseHas('wallet', ['balance'=>5000]) — balance unchanged

TransferDeductsSenderAndCreditsRecipient:
  create sender + wallet with 100000 kobo
  create recipient + wallet with 0
  (new WalletService())->transfer(senderId, recipientId, 50000, 'Test transfer')
  assertDatabaseHas('wallet', ['user_id'=>$sender->id, 'balance'=>50000])
  assertDatabaseHas('wallet', ['user_id'=>$recipient->id, 'balance'=>50000])

TransferFailsWhenAboveDailyLimit:
  create user with wallet 10000000 kobo
  create UserLimit with daily_transfer_limit = 1000 (NGN)
  expect exception when transferring 200000 kobo (₦2000 > ₦1000 limit)

DuplicateWebhookDoesNotDoubleCreditWallet:
  create user + wallet with 0 balance
  credit once with reference 'PAYSTACK-123' → balance 50000
  credit again with same reference → balance still 50000

After creating all tests run:
  php artisan test --stop-on-failure
Show me the output. Fix any failing tests before moving on.
```

---

## SECTION 13 — Performance and Production Readiness

```
Read vite.config.js and check composer.json before starting.

TASK 1 — Eager loading sweep
Open these files and find every Livewire component that loops over users or transactions
without eager loading:
  AdminUsers.php, AdminTransactions.php, AdminKYC.php, DashboardPage.php, Transactions.php

Replace lazy-loading with eager loading:
  User::paginate() → User::with('wallet')->paginate()
  Transaction::get() → Transaction::with('user', 'recipient')->get()

TASK 2 — Vite config check
Open vite.config.js. Confirm input includes:
  'resources/css/app.css'
  'resources/css/bootstrap.css'
  'resources/css/custom.css'
  'resources/js/app.js'

TASK 3 — Custom error pages
Create resources/views/errors/404.blade.php:
  Standalone HTML (no @extends) with full dark purple theme inline
  x-lucide-search-x large icon
  "Page Not Found" heading
  "Go to Dashboard" button

Create resources/views/errors/500.blade.php:
  x-lucide-alert-triangle large icon
  "Something went wrong on our end"
  "Go to Dashboard" button

Both pages should load Bootstrap from CDN so they work even if Vite assets fail.

TASK 4 — Database seeder
Open database/seeders/DatabaseSeeder.php.
Create (or update) it to seed:
  Admin user: name="Admin User", email="admin@carepay.test", password="password", role=1, kyc_verified=true
  Regular user: name="Test User", email="user@carepay.test", password="password", role=0, kyc_verified=true
  Each gets a wallet with 5000000 kobo balance (₦50,000)
  Each gets a UserLimit with single=100000 daily=500000

Run: php artisan migrate:fresh --seed
Show me the output. Fix any errors.

TASK 5 — Final check commands
Run all four and show me the output:
  php artisan route:list --compact
  php artisan migrate:status
  php artisan config:show database
  php artisan about

Flag anything wrong. Fix it before moving to the next section.
```

---

## SECTION 14 — Render + Aiven Deployment Files

```
Create all deployment files for Render (Docker-based hosting) with Aiven MySQL.
Do NOT install Docker on the machine — these files are just uploaded to GitHub and
Render reads them automatically.

Create these files exactly:

FILE 1 — Dockerfile (in project root)
FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx supervisor curl git unzip nodejs npm
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath opcache
RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

FILE 2 — Dockerfile.horizon (in project root)
FROM php:8.2-cli-alpine
RUN apk add --no-cache git unzip
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath opcache
RUN apk add --no-cache autoconf g++ make \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
CMD ["php", "artisan", "horizon"]

FILE 3 — docker/nginx.conf
worker_processes auto;
events { worker_connections 1024; }
http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    sendfile on;
    server {
        listen 8080;
        server_name _;
        root /var/www/html/public;
        index index.php;
        location / { try_files $uri $uri/ /index.php?$query_string; }
        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
        location ~ /\.(?!well-known).* { deny all; }
    }
}

FILE 4 — docker/supervisord.conf
[supervisord]
nodaemon=true
logfile=/var/log/supervisord.log
[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

FILE 5 — docker/php.ini
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000

FILE 6 — render-deploy.sh (in project root)
#!/usr/bin/env bash
set -e
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
echo "deploy done"

Make it executable: chmod +x render-deploy.sh

FILE 7 — .dockerignore (in project root)
/node_modules
/public/hot
/.git
.env
.env.*
*.log
.phpunit.result.cache

After creating all files:
  git add .
  git commit -m "Add Render deployment config"
  git push origin main
Show me confirmation.
```

---

## SECTION 15 — config/database.php: Aiven SSL

```
Open config/database.php.
Find the mysql connection array.
Add the SSL options block inside it so Aiven's SSL-required connection works:

'options' => extension_loaded('pdo_mysql') ? array_filter([
    PDO::MYSQL_ATTR_SSL_CA             => env('MYSQL_ATTR_SSL_CA'),
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
]) : [],

Do not change anything else in the file.
```

---

## SECTION 16 — Final Keys Collection (Do this last)

```
You are now ready to collect all credentials and configure the app for production.
Ask me for these one by one — do not proceed with any step until I answer.
After I give all answers, update .env and config/services.php in one go.

Questions to ask me:

1. "What is your Herd site URL for local development?"
   (e.g. http://carepay.test or the Expose URL https://xxx.sharedwithexpose.com)

2. "What is your MySQL database name, username, and password?"
   (your local Herd MySQL credentials)

3. "What is your Paystack PUBLIC key?" (starts with pk_test_ or pk_live_)

4. "What is your Paystack SECRET key?" (starts with sk_test_ or sk_live_)

5. "Do you have a Mailtrap account for email testing?
   If yes: give me the SMTP username and password.
   If no: I will use the log driver."

6. "Do you want Twilio SMS OTP or email OTP only?
   If Twilio: give me your Account SID, Auth Token, and phone number."

7. "Do you have a VTPass API key for bill payments?
   If yes: give me the key.
   If no: the bill payment will stay mocked until you get one."

8. "Your Aiven MySQL credentials for production:"
   - Host (e.g. mysql-xxx.aivencloud.com)
   - Port (NOT 3306 — it will be something like 14550)
   - Database name (usually defaultdb)
   - Username (usually avnadmin)
   - Password

9. "Have you downloaded the Aiven CA certificate (ca.pem)?
   If yes: confirm you have placed it at storage/certs/aiven-ca.pem in your project."

10. "Your Render app URL after deployment" (e.g. https://carepay-web.onrender.com)
    Say 'not deployed yet' if you haven't deployed yet.

After I answer all 10 questions, update .env with:
  APP_NAME=CarePay
  APP_ENV=local
  APP_DEBUG=true
  APP_URL=<answer 1>
  APP_ASSET_URL=<answer 1>
  DB_CONNECTION=mysql
  DB_DATABASE=<answer 2 database>
  DB_USERNAME=<answer 2 username>
  DB_PASSWORD=<answer 2 password>
  PAYSTACK_PUBLIC_KEY=<answer 3>
  PAYSTACK_SECRET_KEY=<answer 4>
  PAYSTACK_PAYMENT_URL=https://api.paystack.co
  SANCTUM_STATEFUL_DOMAINS=<domain from answer 1 without https://>
  SESSION_DOMAIN=<same domain>
  MAIL_MAILER=<smtp or log based on answer 5>
  QUEUE_CONNECTION=redis
  CACHE_STORE=redis
  SESSION_DRIVER=redis
  (add Twilio vars if answer 6 was yes)
  (add VTPASS_API_KEY if answer 7 was yes)

Update config/services.php to have:
  'paystack' => [
      'public' => env('PAYSTACK_PUBLIC_KEY'),
      'secret' => env('PAYSTACK_SECRET_KEY'),
      'url'    => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
  ],

Then run:
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
  php artisan migrate:status

Show me the output of each command.
Show me a final checklist of every .env key — mark each REQUIRED or OPTIONAL and FILLED or MISSING.
```

---

## File Map (Reference for every session)

| What | Path |
|------|------|
| User model | app/Models/User.php |
| Wallet model | app/Models/Wallet.php |
| Transaction model | app/Models/Transaction.php |
| VirtualAccount model | app/Models/VirtualAccount.php |
| UserLimit model | app/Models/UserLimit.php |
| WalletService | app/Services/walletServices.php |
| TransferService | app/Services/TransferService.php |
| VirtualAccountService | app/Services/VirtualAccountService.php |
| PinService | app/Services/PinService.php |
| PaymentService | app/Services/PaymentServices.php |
| BillPaymentService | app/Services/BillPaymentService.php |
| Webhook controller | app/Http/Controllers/PaystackWebhookController.php |
| SendMoney | app/Livewire/SendMoney.php |
| AddMoney | app/Livewire/AddMoney.php |
| BillPayment | app/Livewire/BillPayment.php |
| DashboardPage | app/Livewire/DashboardPage.php |
| Wallet | app/Livewire/Wallet.php |
| Admin | app/Livewire/Admin/Admin.php |
| AdminUsers | app/Livewire/Admin/AdminUsers.php |
| AdminTransactions | app/Livewire/Admin/AdminTransactions.php |
| AdminKYC | app/Livewire/Admin/AdminKYC.php |
| App layout | resources/views/components/layouts/app.blade.php |
| Admin layout | resources/views/components/layouts/admin.blade.php |
| Send money view | resources/views/livewire/send-money.blade.php |
| Recipient step | resources/views/livewire/steps/recipient-step.blade.php |
| Amount step | resources/views/livewire/steps/amount-step.blade.php |
| Confirm step | resources/views/livewire/steps/confirm-step.blade.php |
| Success step | resources/views/livewire/steps/success-step.blade.php |
| Bank transfer step | resources/views/livewire/steps/deposit-bank-transfer.blade.php |
| Card step | resources/views/livewire/steps/deposit-card.blade.php |
| USSD step | resources/views/livewire/steps/deposit-ussd.blade.php |
| Cash step | resources/views/livewire/steps/deposit-cash.blade.php |
| Dashboard view | resources/views/livewire/dashboard-page.blade.php |
| Wallet view | resources/views/livewire/wallet.blade.php |
| Bill payment view | resources/views/livewire/bill-payment.blade.php |
| Custom CSS | resources/css/custom.css |
| Routes | routes/web.php |
| Middleware | bootstrap/app.php |
| DB config | config/database.php |
| Services config | config/services.php |
| Vite config | vite.config.js |
