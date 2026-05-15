# CarePay — Full-Stack Integration & UI Improvement Prompt
# Paste into GitHub Copilot Chat in Agent Mode (Ctrl+Shift+I → Agent)
# Do ONE section at a time. Wait for Copilot to finish before pasting the next.
# Copilot will ask you for keys and credentials when it needs them.

---

## SECTION 0 — Project Brain (Paste this FIRST and keep it in every session)

```
You are working on CarePay, a Nigerian fintech app.

Stack:
- Laravel 12, PHP 8.2
- Livewire 3 (full-stack reactive components, no API calls from frontend)
- Bootstrap 5 with a custom dark glassmorphism theme
- Lucide icons via x-lucide-{name} blade components (e.g. x-lucide-send, x-lucide-wallet)
- Font Awesome is also loaded but prefer Lucide for all new icons
- Custom CSS classes in resources/css/custom.css:
  card-luxury, btn-gradient, gradient-text, gradient-bg-primary,
  text-muted-custom, text-primary-custom, bg-secondary-custom,
  shadow-primary, shadow-primary-lg, hover-lift, glass-effect,
  blur-circle-primary, blur-circle-accent, icon-container, icon-container-sm,
  icon-container-md, rounded-xl, stat-card
- Blade UI components: x-ui.card, x-ui.button, x-ui.input, x-ui.textarea, x-ui.badge, x-ui.alert
- Paystack for payments and virtual accounts
- WalletService in app/Services/walletServices.php (class WalletService)
- TransferService in app/Services/TransferService.php
- VirtualAccountService in app/Services/VirtualAccountService.php
- PinService in app/Services/PinService.php (PIN is hashed with bcrypt, verified with Hash::check)
- Balance stored in KOBO (1 NGN = 100 kobo). Always convert: amount * 100 to store, / 100 to display.
- Transaction types in DB: 'credit', 'debit' (NOT 'in'/'out' — fix any mismatch you find)
- Wallet table name is 'wallet' (singular — matches migration)
- All amounts in Transaction and Wallet models are in KOBO

Rules you must follow:
- NEVER delete existing logic, methods, or properties — only improve or fix them
- NEVER switch CSS frameworks or remove existing CSS classes
- ALWAYS read the target file before editing it
- ALWAYS use Lucide icons for any new icons (x-lucide-{name})
- Comments should sound like a 16-year-old wrote them — casual, no emojis, plain English
- No documentation blocks, no @param @return tags, no fancy PHPDoc
- Do NOT add markdown documentation files
- Ask me for any API key, credential, or environment value before writing code that needs it
- When you need to know something before continuing, ask me — do not guess
```

---

## SECTION 1 — Critical Bug Fixes (Do this before anything else)

```
Fix these bugs in CarePay. Read every file before touching it.

BUG 1 — app/Models/User.php is broken
The class closes with } before the methods sendEmailVerificationNotification,
hasVerifiedEmail, and markEmailAsVerified. Those three methods are written outside
the class body which is a fatal PHP parse error.

Fix: move all three methods inside the class body. The file should have exactly one
closing } at the very bottom for the class. Keep every single line of existing logic.
While you are in there, make sure the casts array has:
  'pin' => 'hashed',
  'password' => 'hashed',
  'phone' => Encrypted::class,
  'id_number' => Encrypted::class,
and that the class uses SoftDeletes.

BUG 2 — routes/web.php has duplicate routes
/dashboard is registered twice — once outside auth middleware and once inside.
Remove the one outside the auth group (the one that just returns view('dashboard')).
/payment/callback is registered twice at the bottom. Remove the second one.

BUG 3 — Logout is a GET route
Change the logout route to POST. Update any logout link in blade files to use:
  <form method="POST" action="/logout">@csrf<button type="submit">Logout</button></form>
Search all blade files for href containing "logout" and replace with the form.

BUG 4 — Middleware not registered
Open bootstrap/app.php. Register these aliases in the withMiddleware section:
  'active' => App\Http\Middleware\EnsureAccountIsActive::class
  'role'   => App\Http\Middleware\RoleMiddleware::class

Then open app/Http/Middleware/RoleMiddleware.php. If it does not exist, create it.
It should check $request->user()->role:
  0 = regular user, 1 = admin, 2 = super_admin
Usage in routes: ->middleware('role:admin') checks role >= 1
                 ->middleware('role:super_admin') checks role >= 2
Redirect to /dashboard with an error flash message if access is denied.

BUG 5 — PaystackWebhookController method name wrong
routes/web.php calls [PaystackWebhookController::class, 'handleEvent']
but the method in the controller is called handle().
Rename the method in the controller to handleEvent(). Keep all the logic inside it.

BUG 6 — TransferService uses wrong field names
app/Services/TransferService.php creates Transaction records with 'transaction_type'
but the Transaction model $fillable has 'type'. The WalletService already uses 'type' correctly.
Open both files. Check the transactions migration for the real column name.
Make TransferService consistent with WalletService — use whatever the migration says.

BUG 7 — DashboardPage uses wrong transaction type values
app/Livewire/DashboardPage.php filters by transaction_type 'in' and 'out'.
These should be 'credit' and 'debit' to match what WalletService actually stores.
Fix all four places where 'in'/'out' appear in DashboardPage.php.

BUG 8 — Amount step shows hardcoded balance
resources/views/livewire/steps/amount-step.blade.php shows ₦12,450.00 hardcoded.
This should show the real wallet balance from the Livewire component.
In SendMoney.php add a public $walletBalance = 0; property.
Load it in mount() from Auth::user()->wallet->balance / 100.
In amount-step.blade.php replace the hardcoded number with ₦{{ number_format($walletBalance, 2) }}.

BUG 9 — public/hot file
Check if public/hot exists. If it does, delete it. Add public/hot to .gitignore.

After all fixes run:
  php artisan route:list --compact
Show me the output. Flag any duplicate routes.
```

---

## SECTION 2 — Virtual Account: Backend to Frontend Connection

```
The virtual account feature exists in the backend but the frontend shows hardcoded data.
Fix this completely so the frontend reads real data from the database.

--- Step 1: Check the virtual accounts migration ---
Open database/migrations/2026_03_23_123828_create_virtual_accounts_table.php
Check what columns exist. The VirtualAccount model needs:
  user_id, account_number, account_name, bank_name, provider
These should already be in the migration. Confirm and move on.

--- Step 2: Auto-create virtual account on registration ---
Open app/Actions/Fortify/CreateNewUser.php (or wherever new users are created).
After the user is created, check if they already have a virtual account.
If not, dispatch a job to create one:
  CreateVirtualAccountJob::dispatch($user);

Create the job: php artisan make:job CreateVirtualAccountJob
In the job handle() method:
  - check if user already has a virtual account (VirtualAccount::where('user_id', $user->id)->exists())
  - if yes, return early
  - if no, call (new VirtualAccountService())->create($user)
  - wrap in try/catch and log any error with Log::error()
The job should implement ShouldQueue so it runs in the background via Horizon.

Ask me now: "Do you want me to auto-create virtual accounts for existing users who don't have one?
If yes I will make a command you can run once. Say yes or no."
Wait for my answer.

--- Step 3: Fix AddMoney Livewire component ---
Open app/Livewire/AddMoney.php.
The $virtualAccount property is already loaded in mount(). Good.
Add these computed display properties:
  public $accountNumber = '';
  public $accountName   = '';
  public $bankName      = '';
  public $hasVirtualAccount = false;

In mount(), after loading $this->virtualAccount:
  if ($this->virtualAccount) {
      $this->accountNumber      = $this->virtualAccount->account_number;
      $this->accountName        = $this->virtualAccount->account_name;
      $this->bankName           = $this->virtualAccount->bank_name;
      $this->hasVirtualAccount  = true;
  }

--- Step 4: Fix the bank transfer step blade view ---
Open resources/views/livewire/steps/deposit-bank-transfer.blade.php.
Right now it shows:
  Bank Name: "CarePay Virtual Bank" (hardcoded)
  Account Number: "7845621039" (hardcoded)
  Account Name: "John Doe - CarePay" (hardcoded)

Replace all three hardcoded values with the real Livewire properties.
The copy buttons should copy the real values too.

If $hasVirtualAccount is false, show this instead of the account details:
  A card with a x-lucide-clock icon, heading "Setting up your account",
  text "Your dedicated bank account is being created. This usually takes a few seconds.
  Refresh the page in a moment."
  And a "Refresh" button: wire:click="$refresh" wire:poll.5s (auto-refresh every 5 seconds)
  Stop the auto-refresh once the account is available by checking hasVirtualAccount.

Also fix the Font Awesome icons in this file — replace:
  <i class="fas fa-building"> with <x-lucide-building-2 class="w-6 h-6 text-primary-custom" />
  <i class="fas fa-copy">     with <x-lucide-copy class="w-5 h-5" />
  <i class="fas fa-check">    with <x-lucide-check class="w-5 h-5" />
  <i class="fas fa-info-circle"> with <x-lucide-info class="w-5 h-5 text-primary-custom" />

Keep the copy-to-clipboard JavaScript at the bottom of the file — it is working fine.
Just update the icon querySelector to find the lucide svg instead of the i tag.

--- Step 5: Wire card deposit to real Paystack ---
Open app/Livewire/AddMoney.php.
The handleConfirmTransfer() method for card payment currently just inserts into the deposits table.
Replace it with a proper Paystack initialization:

When selectedMethod === 'card' and handleConfirmTransfer() is called:
  1. Validate cardAmount is a number > 100 (minimum ₦100)
  2. Generate a reference: 'DEP_' . strtoupper(Str::random(16))
  3. Store the pending deposit in the deposits table with status='pending' and the reference
  4. Call (new PaymentService())->initialize(
       email: Auth::user()->email,
       amount: (int)($cardAmount * 100), // convert to kobo
       reference: $reference,
       callbackUrl: route('payment.callback')
     )
  5. Get the authorization_url from the response
  6. Redirect the user to Paystack checkout: return redirect()->away($authorizationUrl)

Ask me before writing this: "What is your Paystack public key and secret key?
Also, do you want card deposits to use Paystack Popup (inline JS widget) or
redirect to Paystack checkout page?"
Wait for my answer before writing any code.
```

---

## SECTION 3 — Send Money: OPay-Style Flow

```
The current SendMoney flow uses hardcoded fake contacts and searches by email.
Replace it with a real Nigerian fintech flow like OPay — phone number + bank name lookup.

Read app/Livewire/SendMoney.php and all files in resources/views/livewire/steps/ first.

--- Step 1: Redesign the recipient step ---
Open app/Livewire/SendMoney.php.

Remove the hardcoded $recentContacts array.
Replace loadRecentContacts() with a real database query:

  public function loadRecentContacts()
  {
      // get the last 6 people this user sent money to
      // join to get their name and phone
      $this->recentContacts = Transaction::where('user_id', Auth::id())
          ->where('type', 'debit')
          ->whereNotNull('recipient_id')
          ->with('recipient:id,first_name,last_name,phone,username')
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
                  'phone'    => $r->phone ? substr($r->phone, -4) : '****', // show only last 4 digits
                  'initials' => strtoupper(substr($r->first_name, 0, 1) . substr($r->last_name, 0, 1)),
              ];
          })
          ->filter()
          ->values()
          ->toArray();
  }

Add a searchByPhone() method:
  public $phoneNumber = '';
  public $searchResult = null;
  public $searchError  = '';

  public function searchByPhone()
  {
      // phone number is encrypted so we can't query directly
      // instead, look up by username which is not encrypted
      // ask the user to enter phone or username
      // for now search by username (not encrypted, so queryable)
      $this->searchError = '';
      $this->searchResult = null;

      if (strlen($this->phoneNumber) < 3) {
          $this->searchError = 'Enter at least 3 characters';
          return;
      }

      // search by username (plain text) or by a CarePay tag
      $found = User::where('username', $this->phoneNumber)
          ->orWhere('email', $this->phoneNumber)
          ->where('id', '!=', Auth::id())
          ->where('status', 'active')
          ->first(['id', 'first_name', 'last_name', 'username']);

      if (!$found) {
          $this->searchError = 'No CarePay user found. Check the username or ask them for their CarePay tag.';
          return;
      }

      $this->searchResult = [
          'id'       => $found->id,
          'name'     => $found->first_name . ' ' . $found->last_name,
          'username' => '@' . $found->username,
          'initials' => strtoupper(substr($found->first_name, 0, 1) . substr($found->last_name, 0, 1)),
      ];
  }

  public function confirmSearchResult()
  {
      if ($this->searchResult) {
          $this->selectedRecipient = $this->searchResult;
          $this->setStep('amount');
      }
  }

Note: Phone is encrypted in the database so we cannot query it directly.
We search by username instead which is like a CarePay tag (e.g. @john_doe).
Add a note in the UI explaining this.

Ask me: "Do you want to add an unencrypted 'carepay_tag' column for user lookup,
or should we keep using username? This avoids the encrypted phone problem entirely."
Wait for my answer before changing the User model or migration.

--- Step 2: Redesign recipient-step.blade.php ---
Open resources/views/livewire/steps/recipient-step.blade.php.
Rewrite it to match OPay style:

Top section — "Enter CarePay Tag or Username":
  A large input field with x-lucide-search icon on the left
  Placeholder: "Enter @username or email"
  wire:model.lazy="phoneNumber"
  A "Find" button next to it: wire:click="searchByPhone"
  If searchError show it in small red text below the input
  If searchResult show a result card:
    - Purple avatar circle with initials
    - Name in bold
    - CarePay tag in muted text
    - x-lucide-check-circle icon in green on the right
    - A "Send to this person" button: wire:click="confirmSearchResult" class="btn btn-gradient w-100 mt-2"

Bottom section — "Recent":
  A row of round avatar buttons (like OPay/Kuda quick transfer row)
  Each contact is a circle with their initials (gradient-bg-primary)
  Their name underneath in tiny text (text-truncate, max 8 chars)
  Clicking one calls selectRecipient(contact.id)
  If no recent contacts, show a small muted text "No recent transfers yet"

Replace Font Awesome icons with Lucide:
  <i class="fas fa-search"> → <x-lucide-search class="w-4 h-4 text-muted-custom" />
  <i class="fas fa-arrow-right"> → <x-lucide-arrow-right class="w-4 h-4" />

--- Step 3: Remove the "Method" step ---
The current flow has 5 steps: recipient → amount → method → confirm → success.
The "method" step (wallet/bank/card) makes no sense for internal transfers — 
CarePay-to-CarePay always uses the wallet balance.
Remove the method step entirely:
  - In SendMoney.php change: recipient → amount → confirm → success
  - Remove the method step from $steps array in getStepIndex()
  - The setStep('method') in handleAmountSubmit() should now setStep('confirm')
  - Remove the method-step.blade.php include from send-money.blade.php
  - Remove the method step from the progress indicator in send-money.blade.php
  - In handleConfirm(), always use WalletService::transfer() (wallet balance), not method

Update the progress indicator in send-money.blade.php to show only 4 circles:
  Recipient → Amount → Confirm → Done

--- Step 4: Wire handleConfirm to WalletService ---
In SendMoney.php, update handleConfirm() to use WalletService properly:

  public function handleConfirm()
  {
      $this->isProcessing = true;
      $this->errorMessage = '';

      try {
          $sender = Auth::user();
          $recipientId = $this->selectedRecipient['id'] ?? null;

          if (!$recipientId) {
              throw new \Exception('Please select a recipient first.');
          }

          $amountInKobo = (int) round(floatval($this->amount) * 100);

          if ($amountInKobo < 100) {
              throw new \Exception('Minimum transfer amount is ₦1.');
          }

          // check daily limit before doing anything
          $limits = $sender->limits;
          if ($limits) {
              $todaySpent = Transaction::where('user_id', $sender->id)
                  ->where('type', 'debit')
                  ->whereDate('created_at', today())
                  ->sum('amount'); // this is in kobo

              $singleLimitKobo = $limits->single_transaction_limit * 100;
              $dailyLimitKobo  = $limits->daily_transfer_limit * 100;

              if ($amountInKobo > $singleLimitKobo) {
                  throw new \Exception('Amount exceeds your single transaction limit of ₦' . number_format($limits->single_transaction_limit, 2));
              }

              if (($todaySpent + $amountInKobo) > $dailyLimitKobo) {
                  $remaining = ($dailyLimitKobo - $todaySpent) / 100;
                  throw new \Exception('This would exceed your daily limit. You can send up to ₦' . number_format($remaining, 2) . ' more today.');
              }
          }

          $walletService = new \App\Services\WalletService();
          $result = $walletService->transfer(
              senderId:     $sender->id,
              recipientId:  $recipientId,
              amountInKobo: $amountInKobo,
              description:  $this->note ?: 'Transfer'
          );

          $this->currentStep = 'success';
          $this->dispatch('toast', type: 'success', message: 'Transfer successful!');

      } catch (\Exception $e) {
          $this->errorMessage = $e->getMessage();
          $this->dispatch('toast', type: 'error', message: $e->getMessage());
      } finally {
          $this->isProcessing = false;
      }
  }

--- Step 5: Update the success step ---
Open resources/views/livewire/steps/success-step.blade.php.
Replace any Font Awesome icons with Lucide.
Add the transaction reference from the WalletService result.
In SendMoney.php add: public $transferReference = '';
After successful transfer set: $this->transferReference = $result['reference'];
Show it in the success step as a reference number the user can screenshot.
```

---

## SECTION 4 — Dashboard: Intensive UI Improvement

```
Improve the dashboard massively. Keep all existing PHP logic in DashboardPage.php —
only improve the blade view and add the chart.

Read resources/views/livewire/dashboard-page.blade.php and app/Livewire/DashboardPage.php first.

--- Step 1: Fix the transaction type filter bug ---
DashboardPage.php filters by transaction_type 'in' and 'out'. These are wrong.
Change:
  ->where('transaction_type', 'in')  →  ->where('type', 'credit')
  ->where('transaction_type', 'out') →  ->where('type', 'debit')
Do this in all 4 places in loadData().

--- Step 2: Add chart data to DashboardPage.php ---
Add a public $chartData = '{}'; property.
At the bottom of loadData(), after the existing queries, add:

  // get last 6 months of income and expenses for the chart
  $chartMonths  = [];
  $chartIncome  = [];
  $chartExpense = [];

  for ($i = 5; $i >= 0; $i--) {
      $month = now()->subMonths($i);
      $chartMonths[] = $month->format('M');

      $inc = Transaction::where('user_id', $userId)
          ->where('type', 'credit')
          ->whereYear('created_at', $month->year)
          ->whereMonth('created_at', $month->month)
          ->sum('amount'); // kobo

      $exp = Transaction::where('user_id', $userId)
          ->where('type', 'debit')
          ->whereYear('created_at', $month->year)
          ->whereMonth('created_at', $month->month)
          ->sum('amount'); // kobo

      $chartIncome[]  = round($inc / 100, 2); // convert to naira for display
      $chartExpense[] = round($exp / 100, 2);
  }

  $this->chartData = json_encode([
      'labels'   => $chartMonths,
      'income'   => $chartIncome,
      'expenses' => $chartExpense,
  ]);

--- Step 3: Rewrite the dashboard blade view ---
Open resources/views/livewire/dashboard-page.blade.php.
Keep all the existing structure but improve these specific areas:

IMPROVEMENT A — Balance card:
Replace the blur circle divs with actual CSS (they might not be defined).
Add a "Available Balance" label with x-lucide-wallet icon (class="w-5 h-5").
The balance amount should be display-4 not display-3 on mobile.
Add a thin progress bar below the balance showing how close the user is to their daily limit:
  Load $dailyLimitUsedPercent in DashboardPage.php:
    $todaySpent = Transaction::where('user_id', $userId)->where('type','debit')->whereDate('created_at', today())->sum('amount');
    $dailyLimit = optional($user->limits)->daily_transfer_limit ?? 500000; // kobo
    $this->dailyLimitUsedPercent = $dailyLimit > 0 ? min(100, round(($todaySpent / ($dailyLimit * 100)) * 100)) : 0;
  In the blade:
    <div class="mt-3">
      <div class="d-flex justify-content-between small opacity-75 mb-1">
        <span>Daily limit used</span>
        <span>{{ $dailyLimitUsedPercent }}%</span>
      </div>
      <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px;">
        <div class="progress-bar" style="width: {{ $dailyLimitUsedPercent }}%; background: #c084fc;"></div>
      </div>
    </div>

IMPROVEMENT B — Replace the spending chart placeholder:
Remove the placeholder div entirely. Replace with:
  <div style="position: relative; height: 250px;">
    <canvas id="spendingChart"></canvas>
  </div>
  
  @push('scripts')
  <script>
  // this sets up the chart after the page loads
  document.addEventListener('livewire:navigated', () => initChart());
  document.addEventListener('DOMContentLoaded', () => initChart());

  function initChart() {
      const el = document.getElementById('spendingChart');
      if (!el) return;
      if (el._chart) el._chart.destroy(); // destroy old chart before making new one

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
                      backgroundColor: 'rgba(168, 85, 247, 0.1)',
                      borderWidth: 2,
                      pointBackgroundColor: '#a855f7',
                      pointRadius: 4,
                      fill: true,
                      tension: 0.4,
                  },
                  {
                      label: 'Expenses',
                      data: data.expenses,
                      borderColor: '#c084fc',
                      backgroundColor: 'rgba(192, 132, 252, 0.05)',
                      borderWidth: 2,
                      pointBackgroundColor: '#c084fc',
                      pointRadius: 4,
                      fill: true,
                      tension: 0.4,
                  }
              ]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: { labels: { color: '#888', font: { size: 11 } } }
              },
              scales: {
                  x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#888' } },
                  y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#888', callback: v => '₦' + v.toLocaleString() } }
              }
          }
      });
  }
  </script>
  @endpush

Load Chart.js in resources/views/components/layouts/app.blade.php from CDN:
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
Put this in the <head> tag, before @livewireScripts.
Also add @stack('scripts') just before </body>.

IMPROVEMENT C — Stat cards:
Replace the existing 4 stat cards content to use proper Lucide icons.
Income card: x-lucide-arrow-down-circle (green, not purple)
Expenses card: x-lucide-arrow-up-circle (red)
Transactions card: x-lucide-repeat
Bills paid card: x-lucide-receipt

Also fix the balance display: wallet stores kobo, so divide by 100 before displaying:
In DashboardPage.php loadData():
  $this->balance = $wallet->balance / 100; // convert kobo to naira for display
  $this->monthlyIncome = ... sum('amount') / 100; // same for all amount sums

IMPROVEMENT D — Recent transactions list:
The existing list shows type 'in'/'out'. Fix to show type 'credit'/'debit'.
For each transaction show:
  - Lucide icon: x-lucide-arrow-down-left for credit (green), x-lucide-arrow-up-right for debit (purple)
  - Description text (use $tx->description if available, else 'Transfer')
  - Date formatted as "May 12" (short format)
  - Amount: +₦XX,XXX for credit, -₦XX,XXX for debit (divide by 100 for display)
  - Status badge: if status is not 'success', show a small badge in warning color

IMPROVEMENT E — Replace Font Awesome icons in dashboard:
Search the dashboard blade for any <i class="fas or <i class="fa- and replace with Lucide:
  fa-refresh / fa-sync → x-lucide-refresh-cw
  fa-bell → x-lucide-bell
  fa-arrow-up-right → x-lucide-arrow-up-right
  fa-arrow-down-left → x-lucide-arrow-down-left
  fa-send → x-lucide-send
  fa-plus → x-lucide-plus
  fa-receipt → x-lucide-receipt
  fa-calendar → x-lucide-calendar
  fa-wallet → x-lucide-wallet
  fa-bar-chart → x-lucide-bar-chart-3
```

---

## SECTION 5 — Add Money Page: Full UI Overhaul

```
The add-money page needs a full UI upgrade. Keep all existing PHP logic.
Read resources/views/livewire/add-money.blade.php and all 
resources/views/livewire/steps/deposit-*.blade.php files first.

--- Step 1: Method selection step ---
Open resources/views/livewire/add-money.blade.php (the main container view).
Improve the method selection step (step === 'select'):

Show 4 method cards in a 2x2 grid (col-6 each):
Each card is a card-luxury with hover-lift, clickable via wire:click="handleMethodSelect('method-name')"

Bank Transfer card:
  Icon: x-lucide-building-2 (large, in a purple icon-container-md)
  Title: "Bank Transfer" in fw-bold
  Subtitle: "Transfer from any Nigerian bank" in small text-muted-custom
  Badge: "Free" in green (small badge, bg success text-white rounded-pill)

Debit Card card:
  Icon: x-lucide-credit-card
  Title: "Debit Card"
  Subtitle: "Instant deposit with your card"
  Badge: "1.5% fee" in yellow (bg-warning text-dark)

USSD card:
  Icon: x-lucide-smartphone
  Title: "USSD"
  Subtitle: "Dial a code from any phone"
  Badge: "Free" in green

Cash card:
  Icon: x-lucide-banknote
  Title: "Cash Deposit"
  Subtitle: "Deposit at an agent near you"
  Badge: "Agent fee may apply" in muted text-muted-custom text

At the top of this step show the current balance card (small):
  A card with x-lucide-wallet icon + "Current Balance" + ₦{{ number_format($currentBalance / 100, 2) }}
  Use card-luxury styling, purple border

--- Step 2: Bank transfer step ---
Open resources/views/livewire/steps/deposit-bank-transfer.blade.php.
This is already done in Section 2. Confirm the Lucide icons are there.

--- Step 3: USSD step ---
Open resources/views/livewire/steps/deposit-ussd.blade.php.
Replace any Font Awesome icons with Lucide.
The USSD step should show:
  A dropdown to select bank (use a <select> with wire:model="selectedBank")
  Banks list: GTBank, Access Bank, First Bank, Zenith Bank, UBA, Fidelity, Sterling, Polaris
  After selecting bank, show the USSD code to dial:
    GTBank: *737*50*amount*account_number#
    Access Bank: *901*amount*account_number#
    First Bank: *894*amount*account_number#
    Zenith Bank: *966*amount*account_number#
    UBA: *919*amount*account_number#
    Others: show "Dial *XXX# and follow prompts" with the account number to transfer to.
  The account number in the USSD code should come from $accountNumber (the virtual account).
  If no virtual account yet, show the "Setting up your account" card from Section 2.
  A big "Open Dialer" button that links to tel: with the USSD code pre-filled:
    <a href="tel:{{ urlencode($ussdCode) }}" class="btn btn-gradient w-100 py-3">Open Dialer</a>

--- Step 4: Cash deposit step ---
Open resources/views/livewire/steps/deposit-cash.blade.php.
Replace any Font Awesome icons with Lucide.
Show:
  Info card explaining how to find a CarePay agent
  A search bar (just a UI element, no backend needed yet — add a comment saying "search not wired yet")
  A list of dummy agent locations (2-3 items showing name, area, and "Open Now" badge)
  Each agent item uses card-luxury, x-lucide-map-pin icon, x-lucide-phone icon for contact
  Instructions card with x-lucide-info icon:
    - Go to a CarePay agent near you
    - Tell them your phone number or show your CarePay tag (@username)
    - They will credit your wallet after you pay cash

--- Step 5: Success step ---
Open resources/views/livewire/steps/deposit-success.blade.php.
Improve it:
  Big x-lucide-check-circle icon in green (w-16 h-16)
  "Deposit Initiated" as heading
  Subtext based on method:
    bank-transfer: "Your wallet will be credited when we receive your transfer. Usually 1-5 minutes."
    card: "Your card has been charged. Wallet credited instantly."
    ussd: "Complete the USSD transaction on your phone. Wallet credited in 1-2 minutes."
    cash: "Ask your agent to confirm the deposit. Wallet credited immediately."
  Two buttons:
    "View Wallet" → route('wallet')
    "Go to Dashboard" → route('dashboard')
  Both use d-flex gap-2 layout
```

---

## SECTION 6 — App Layout: Mobile Navigation + Toast System

```
Improve the main app layout that wraps all authenticated pages.
Read resources/views/components/layouts/app.blade.php first.

--- Step 1: Fix the layout base ---
The current app.blade.php is extremely minimal — just a body with $slot.
Update it to:

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'CarePay' }}</title>
    @vite(['resources/css/app.css', 'resources/css/bootstrap.css', 'resources/css/custom.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireStyles
</head>
<body class="bg-dark-custom" style="background: #0a0a0f; color: white; min-height: 100vh;">

    {{-- top navbar for desktop --}}
    <nav class="navbar sticky-top d-none d-md-flex glass-effect border-bottom py-2" 
         style="background: rgba(10,10,15,0.85); backdrop-filter: blur(10px); border-color: rgba(168,85,247,0.15) !important;">
        <div class="container-fluid">
            <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center gap-2">
                <div class="icon-container icon-container-sm gradient-bg-primary">
                    <x-lucide-wallet class="text-white w-5 h-5" />
                </div>
                <span class="gradient-text fw-bold fs-5">CarePay</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <button class="btn btn-link text-muted-custom p-1 position-relative" 
                        data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas">
                    <x-lucide-bell class="w-5 h-5" />
                    {{-- notification dot --}}
                </button>
                <a href="{{ route('profile') }}" class="text-decoration-none">
                    <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width: 34px; height: 34px; font-size: 13px;">
                        {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                    </div>
                </a>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted-custom p-1">
                        <x-lucide-log-out class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- main content --}}
    <main class="pb-5 pb-md-0" style="padding-bottom: 80px !important;">
        {{-- on mobile, add top padding so content doesn't hide behind fixed mobile header --}}
        <div class="pt-3 pt-md-0 px-3 px-md-4 container-fluid" style="max-width: 1200px; margin: 0 auto;">
            {{ $slot }}
        </div>
    </main>

    {{-- mobile top header with back button area --}}
    <div class="d-flex d-md-none align-items-center px-3 py-2 sticky-top"
         style="background: rgba(10,10,15,0.9); backdrop-filter: blur(10px); top: 0; z-index: 100; border-bottom: 1px solid rgba(168,85,247,0.1);">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="icon-container icon-container-sm gradient-bg-primary">
                <x-lucide-wallet class="text-white" style="width:14px; height:14px;" />
            </div>
            <span class="gradient-text fw-bold" style="font-size: 15px;">CarePay</span>
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <button class="btn btn-link text-muted-custom p-1" data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas">
                <x-lucide-bell style="width:18px; height:18px;" />
            </button>
            <a href="{{ route('profile') }}" class="text-decoration-none">
                <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width: 30px; height: 30px; font-size: 11px;">
                    {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                </div>
            </a>
        </div>
    </div>

    {{-- mobile bottom nav bar --}}
    <nav class="d-flex d-md-none fixed-bottom border-top"
         style="background: rgba(10,10,15,0.95); backdrop-filter: blur(20px); border-color: rgba(168,85,247,0.15) !important; z-index: 200;">
        @php
            $currentRoute = request()->route()->getName();
        @endphp
        @foreach([
            ['route' => 'dashboard',    'icon' => 'layout-dashboard', 'label' => 'Home'],
            ['route' => 'send-money',   'icon' => 'send',             'label' => 'Send'],
            ['route' => 'add-money',    'icon' => 'plus-circle',      'label' => 'Add'],
            ['route' => 'transactions', 'icon' => 'list',             'label' => 'History'],
            ['route' => 'profile',      'icon' => 'user',             'label' => 'Profile'],
        ] as $item)
            @php $isActive = $currentRoute === $item['route']; @endphp
            <a href="{{ route($item['route']) }}" 
               class="flex-fill d-flex flex-column align-items-center justify-content-center py-2 text-decoration-none"
               style="color: {{ $isActive ? '#a855f7' : '#666' }};">
                <x-dynamic-component 
                    :component="'lucide-' . $item['icon']" 
                    style="width:20px; height:20px; {{ $isActive ? 'color: #a855f7;' : 'color: #666;' }}" />
                <span style="font-size: 9px; margin-top: 2px; {{ $isActive ? 'color: #a855f7; font-weight: 600;' : 'color: #666;' }}">
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </nav>

    {{-- toast notification container --}}
    <div id="toast-container" 
         style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 320px;">
    </div>

    @livewireScripts
    @stack('scripts')

    <script>
    // listens for toast events from Livewire and shows them on screen
    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', (event) => {
            const type    = event.type    || 'info';
            const message = event.message || '';

            const colors = {
                success: '#22c55e',
                error:   '#ef4444',
                info:    '#a855f7',
                warning: '#f59e0b',
            };

            const icons = {
                success: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>',
                error:   '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                info:    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
                warning: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            };

            const toast = document.createElement('div');
            toast.style.cssText = `
                background: #1a1a24;
                border: 1px solid ${colors[type] || '#a855f7'};
                border-radius: 10px;
                padding: 12px 16px;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.4);
                animation: slideIn 0.2s ease;
                color: white;
                font-size: 13px;
            `;
            toast.innerHTML = `
                <span style="color: ${colors[type]}; flex-shrink: 0;">${icons[type] || ''}</span>
                <span>${message}</span>
            `;

            document.getElementById('toast-container').appendChild(toast);

            // remove after 4 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        });
    });
    </script>

    <style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to   { transform: translateX(0);   opacity: 1; }
    }
    </style>

</body>
</html>

After writing this file, check all authenticated Livewire components to make sure
they return a view() without specifying a layout — Livewire should pick up app.blade.php
automatically as the layout since it's the default.
If any component uses ->layout('components.layouts.app') explicitly, that is fine too.
```

---

## SECTION 7 — Transaction Limits: Enforce Everywhere

```
The UserLimit model and migration exist but limits are not enforced anywhere.
Fix this now.

Read:
  app/Models/UserLimit.php
  app/Services/WalletService.php
  app/Livewire/SendMoney.php (already updated in Section 3)

--- Step 1: Add limit check to WalletService::transfer() ---
Open app/Services/WalletService.php.
In the transfer() method, BEFORE the DB::transaction block, add limit checking:

  // load the sender's limits
  $senderLimits = \App\Models\UserLimit::where('user_id', $senderId)->first();

  if ($senderLimits) {
      $singleLimitKobo = $senderLimits->single_transaction_limit * 100;
      $dailyLimitKobo  = $senderLimits->daily_transfer_limit * 100;

      // check single transaction limit
      if ($amountInKobo > $singleLimitKobo) {
          throw new \Exception('Amount exceeds your single transaction limit of ₦' . number_format($senderLimits->single_transaction_limit, 2) . '.');
      }

      // add up everything the sender spent today
      $todaySpent = Transaction::where('user_id', $senderId)
          ->where('type', 'debit')
          ->whereDate('created_at', today())
          ->sum('amount');

      if (($todaySpent + $amountInKobo) > $dailyLimitKobo) {
          $canStillSend = max(0, $dailyLimitKobo - $todaySpent) / 100;
          throw new \Exception('Daily limit reached. You can send up to ₦' . number_format($canStillSend, 2) . ' more today.');
      }
  }

By putting this in WalletService, it is enforced for ALL transfers regardless of which
Livewire component triggers it. The SendMoney component already has a client-side check
from Section 3 — that is fine as an extra layer. The service is the real enforcer.

--- Step 2: Show limit info in the send money amount step ---
Open resources/views/livewire/steps/amount-step.blade.php.
Below the available balance line, add:
  @if($limits)
  <div class="mt-1 small text-muted-custom">
      Daily limit: ₦{{ number_format($limits->daily_transfer_limit, 2) }}
      &nbsp;·&nbsp;
      Today used: ₦{{ number_format($todaySpent / 100, 2) }}
  </div>
  @endif

In SendMoney.php mount(), add:
  public $limits = null;
  public $todaySpent = 0;

  // in mount():
  $user = Auth::user();
  $this->limits = $user->limits;
  $this->todaySpent = Transaction::where('user_id', $user->id)
      ->where('type', 'debit')
      ->whereDate('created_at', today())
      ->sum('amount'); // in kobo

Pass these to the view in render().
```

---

## SECTION 8 — PIN Verification Before Transfer

```
The PIN verification system exists (PinService, VerifyPin Livewire component, PinModelClass)
but it is not wired into the send money flow. Fix this.

Read:
  app/Livewire/Security/VerifyPin.php
  app/Livewire/Security/PinModelClass.php
  resources/views/livewire/security/verify-pin.blade.php
  resources/views/livewire/security/pin-model-class.blade.php

--- Step 1: Understand the existing PIN flow ---
Tell me what VerifyPin.php does — read it and explain the dispatch events it fires.
Then continue with the fix.

--- Step 2: Add PIN modal to confirm step ---
Open resources/views/livewire/steps/confirm-step.blade.php.
The "Confirm & Send" button currently calls wire:click="handleConfirm".
Instead, it should open a PIN entry modal first.

Add a PIN modal at the bottom of confirm-step.blade.php:
  A Bootstrap modal with id="pinModal"
  Inside: a title "Enter your PIN", 4 individual digit inputs (each type="number" max="1")
  Or use a single 4-digit input (type="password" maxlength="4" pattern="[0-9]*")
  A "Confirm Transfer" button that calls wire:click="verifyAndSend"
  An error message area for wrong PIN feedback

In SendMoney.php:
  public $pin = '';
  public $pinError = '';
  public $showPinModal = false;

  // change handleConfirm to just open the PIN modal
  public function handleConfirm()
  {
      $this->pin = '';
      $this->pinError = '';
      $this->showPinModal = true;
      $this->dispatch('open-pin-modal');
  }

  // new method that actually processes after PIN check
  public function verifyAndSend()
  {
      $this->pinError = '';
      try {
          $user = Auth::user();
          (new \App\Services\PinService())->verify($user, $this->pin);
          // PIN is correct, now do the transfer
          $this->processTransfer();
      } catch (\Exception $e) {
          $this->pinError = $e->getMessage();
      }
  }

  // move all the actual transfer logic from handleConfirm into this private method
  private function processTransfer()
  {
      // ... the WalletService::transfer() code from Section 3 goes here
  }

Add JavaScript to listen for the open-pin-modal event and show the Bootstrap modal:
  document.addEventListener('livewire:init', () => {
      Livewire.on('open-pin-modal', () => {
          const modal = new bootstrap.Modal(document.getElementById('pinModal'));
          modal.show();
      });
  });

Put this script in @push('scripts') at the bottom of send-money.blade.php.

--- Step 3: Handle users who have not set a PIN yet ---
In verifyAndSend(), before calling PinService::verify():
  if (!$user->pin) {
      $this->pinError = 'You have not set a transaction PIN yet. Go to Settings to create one.';
      return;
  }
```

---

## SECTION 9 — Wallet Page UI Improvement

```
Improve the wallet page. Keep all PHP logic in app/Livewire/Wallet.php.
Read resources/views/livewire/wallet.blade.php first.

Replace or improve these areas:

--- Balance section ---
Show a card-luxury with:
  x-lucide-wallet icon + "My Wallet" label
  Big balance number: ₦{{ number_format($balance / 100, 2) }}
  Three small rows below: Available | Pending | Reserved (from existing $balanceData)
  Toggle balance visibility button (wire:click="toggleBalance") with x-lucide-eye / x-lucide-eye-off
  Two action buttons: "Add Money" → route('add-money'), "Send Money" → route('send-money')
  Style the two buttons as btn-gradient and btn-outline-light

--- Transaction list ---
The existing $transactions is loaded. Show them as a list.
Each item:
  Left: icon circle (green for credit, purple for debit) with x-lucide-arrow-down-left or x-lucide-arrow-up-right
  Center: description (use $tx->description, fallback to 'Transfer'), date in small muted text
  Right: amount in bold (+ for credit, - for debit) in green or muted color
If $transactions is empty, show:
  x-lucide-inbox class="w-12 h-12" style="opacity: 0.3"
  "No transactions yet" in h6 text-muted-custom
  A link to add-money and send-money

--- Scheduled payments ---
Show a separate section "Upcoming Payments" only if $scheduledPayments has items.
Each item:
  x-lucide-calendar icon + description + date + amount
  A "Cancel" button (wire:click="cancelScheduledPayment(id)") — add this method to Wallet.php
  cancelScheduledPayment just sets status = 'cancelled' on the ScheduledPayment record

--- Wallet status indicator ---
If $walletStatus is not 'active', show a warning alert at the top:
  x-lucide-alert-triangle + "Your wallet is currently {status}. Contact support."
  Use bg-warning text-dark styling
```

---

## SECTION 10 — Environment Setup for Herd

```
Before writing any code for this section, ask me these questions one by one:

1. What is your Herd site URL? (e.g. carepay.test or the herd share expose URL)
2. What is your MySQL database name?
3. What is your MySQL username and password?
4. What is your Paystack public key?
5. What is your Paystack secret key?
6. Do you have a Mailtrap account? If yes, give me the username and password.
   If no, I will set MAIL_MAILER=log so emails go to the log file.
7. Do you want Twilio SMS OTP or email OTP only?

Wait for all my answers. Then:

Update .env:
  APP_NAME=CarePay
  APP_URL=<my answer 1>
  APP_ASSET_URL=<my answer 1>
  DB_DATABASE=<my answer 2>
  DB_USERNAME=<my answer 3 username>
  DB_PASSWORD=<my answer 3 password>
  PAYSTACK_PUBLIC_KEY=<my answer 4>
  PAYSTACK_SECRET_KEY=<my answer 5>
  SANCTUM_STATEFUL_DOMAINS=<extract the domain from answer 1, no https://>
  SESSION_DOMAIN=<same domain>
  If Mailtrap: fill MAIL credentials
  If no Mailtrap: MAIL_MAILER=log

Update config/services.php. Make sure this block exists:
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

Show me confirmation of each command.

Then run:
  php artisan migrate:status

Show me the output. If any migrations are pending, run php artisan migrate.
```

---

## SECTION 11 — Final Wiring Check

```
Do a full wiring check. Read each file before checking.

Check 1 — WalletService is used, not TransferService for wallet-to-wallet transfers:
  Open app/Livewire/SendMoney.php
  Confirm it uses WalletService::transfer(), not TransferService::transfer()
  If it still uses TransferService, update it. WalletService has better locking, ledger entries, and limit checks.

Check 2 — VirtualAccountService is called during registration:
  Confirm the job CreateVirtualAccountJob exists and is dispatched in CreateNewUser.php
  Confirm the job is in app/Jobs/
  Run: php artisan horizon:status or php artisan queue:listen (choose based on your setup)
  Ask me: "Are you running Horizon or queue:listen?"

Check 3 — PaystackWebhookController credits the wallet correctly:
  Open the controller. After signature verification:
  - It should find the VirtualAccount by account number
  - Find the wallet by user_id
  - Convert amount from kobo (Paystack sends kobo) → already kobo, no conversion needed
  - Call WalletService::credit() NOT wallet->increment() directly
  - WalletService::credit() handles duplicate prevention with the reference check
  If it still uses wallet->increment(), update it to use WalletService::credit()

Check 4 — Balance display is correct (kobo vs naira):
  In DashboardPage.php: $this->balance should be wallet->balance / 100
  In Wallet.php: $this->balance = $wallet->balance (keep in kobo, divide in the blade)
  In dashboard blade: ₦{{ number_format($balance, 2) }} (balance already divided)
  In wallet blade: ₦{{ number_format($balance / 100, 2) }}
  Fix any inconsistency you find.

Check 5 — All Font Awesome icons replaced with Lucide:
  Search all blade files in resources/views/livewire/ for "fas fa-" or "far fa-"
  List every file that still has Font Awesome icons
  Replace them all with the Lucide equivalent using x-lucide-{name}
  Common replacements:
    fa-check        → lucide-check
    fa-times / fa-x → lucide-x
    fa-copy         → lucide-copy
    fa-eye          → lucide-eye
    fa-eye-slash    → lucide-eye-off
    fa-arrow-left   → lucide-arrow-left
    fa-arrow-right  → lucide-arrow-right
    fa-user         → lucide-user
    fa-cog / fa-gear → lucide-settings
    fa-sign-out     → lucide-log-out
    fa-home         → lucide-home
    fa-lock         → lucide-lock
    fa-unlock       → lucide-unlock
    fa-exclamation-triangle → lucide-alert-triangle
    fa-info-circle  → lucide-info
    fa-question-circle → lucide-help-circle
    fa-spinner      → lucide-loader-2 (add: class="animate-spin" or use Bootstrap spinner)

Check 6 — Routes are clean:
  php artisan route:list --compact
  Show me the output. Confirm no duplicates. Confirm /test route is removed.

Check 7 — App boots without errors:
  php artisan about
  Show me the output.
  Then visit the home page and login page in your browser.
  Tell me any errors you see.
```

---

## Known File Map (Reference)

| What | File |
|------|------|
| User model | app/Models/User.php |
| Wallet model | app/Models/Wallet.php |
| Transaction model | app/Models/Transaction.php |
| VirtualAccount model | app/Models/VirtualAccount.php |
| UserLimit model | app/Models/UserLimit.php |
| WalletService | app/Services/walletServices.php (class WalletService) |
| TransferService | app/Services/TransferService.php |
| VirtualAccountService | app/Services/VirtualAccountService.php |
| PinService | app/Services/PinService.php |
| PaymentService | app/Services/PaymentServices.php (class PaymentService) |
| Paystack webhook | app/Http/Controllers/PaystackWebhookController.php |
| SendMoney Livewire | app/Livewire/SendMoney.php |
| AddMoney Livewire | app/Livewire/AddMoney.php |
| Dashboard Livewire | app/Livewire/DashboardPage.php |
| Wallet Livewire | app/Livewire/Wallet.php |
| BillPayment Livewire | app/Livewire/BillPayment.php |
| App layout | resources/views/components/layouts/app.blade.php |
| Send money main view | resources/views/livewire/send-money.blade.php |
| Recipient step | resources/views/livewire/steps/recipient-step.blade.php |
| Amount step | resources/views/livewire/steps/amount-step.blade.php |
| Confirm step | resources/views/livewire/steps/confirm-step.blade.php |
| Success step | resources/views/livewire/steps/success-step.blade.php |
| Bank transfer step | resources/views/livewire/steps/deposit-bank-transfer.blade.php |
| Card step | resources/views/livewire/steps/deposit-card.blade.php |
| USSD step | resources/views/livewire/steps/deposit-ussd.blade.php |
| Cash step | resources/views/livewire/steps/deposit-cash.blade.php |
| Deposit success | resources/views/livewire/steps/deposit-success.blade.php |
| Dashboard view | resources/views/livewire/dashboard-page.blade.php |
| Wallet view | resources/views/livewire/wallet.blade.php |
| Custom CSS | resources/css/custom.css |
| Routes | routes/web.php |
| Middleware reg | bootstrap/app.php |
| Services config | config/services.php |
