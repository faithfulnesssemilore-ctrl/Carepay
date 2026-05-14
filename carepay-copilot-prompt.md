# CarePay — GitHub Copilot Full Rebuild Prompt

> **How to use this:** Open your CarePay project in VS Code. Open GitHub Copilot Chat (`Ctrl+Shift+I` / `Cmd+Shift+I`). Switch to **Agent mode** (the dropdown next to the text box). Paste each section below one at a time, in order. Copilot will ask you for keys and any info it needs as it goes.

---

## SECTION 1 — Project Context (Paste this first, always keep it at the top)

```
You are working on CarePay — a Nigerian fintech web application built with:
- Laravel 12 (PHP 8.2+)
- Livewire 3 + Volt
- Laravel Fortify (auth) + Sanctum (API tokens)
- Laravel Horizon (queue dashboard)
- Bootstrap 5 + custom dark purple glassmorphism CSS theme
- Paystack (payments/webhooks)
- ichtrojan/laravel-otp (OTP)
- MySQL database (table: wallet is singular, not wallets — match migrations)
- Blade component library under resources/views/components/ui/
- Lucide icons via mallardduck/blade-lucide-icons

The app has these user-facing pages: Home (landing), Login, Register, Dashboard, Wallet, 
Send Money (multi-step), Add Money (multi-step: card/bank transfer/USSD/cash), 
Bill Payment, Transactions, Profile, Settings.

Admin panel: /admin/dashboard, /admin/kyc, /admin/transactions, /admin/users
Super admin: /super-admin/dashboard

The design language is: dark backgrounds (#0a0a0f, #141420, #1a1a24), 
purple accent (#a855f7, #c084fc), glassmorphism cards, Lucide icons, Bootstrap grid.
Keep ALL existing design decisions — only improve quality, fix bugs, and complete missing features.
Do NOT switch CSS frameworks, do NOT remove existing components.

Before making any change, read the relevant existing file first.
After every fix, tell me what you changed and why.
If you need an API key, environment variable, or any credential from me, ask before writing code.
```

---

## SECTION 2 — Critical Bug Fixes (Paste after Section 1)

```
Fix all critical bugs in CarePay. Work through them in this exact order:

--- BUG 1: User.php broken class structure ---
File: app/Models/User.php

The class has TWO closing braces and methods written outside the class body.
The methods sendEmailVerificationNotification(), hasVerifiedEmail(), and markEmailAsVerified()
are declared AFTER the first closing brace of the class — this is a fatal PHP error.

Fix: Restructure the file so the class has exactly ONE closing brace at the very end,
and ALL methods (including the three above) are inside the class body.
Also add the SoftDeletes trait that's missing from the use statement.
Keep all existing logic exactly as-is, just fix the structure.

--- BUG 2: Duplicate and conflicting routes ---
File: routes/web.php

Problems:
1. /dashboard is defined TWICE — once outside auth middleware (returns a plain view) 
   and once inside the auth+active group (returns DashboardPage Livewire component).
   Remove the duplicate outside the middleware group.
2. /payment/callback is defined TWICE at the bottom of the file.
   Remove the second duplicate.
3. The /logout route uses GET — this is a security issue. Change it to POST with CSRF protection,
   and update any logout links/buttons in Blade views to use a form with @csrf.

--- BUG 3: Middleware not registered ---
File: bootstrap/app.php

The routes use ->middleware('role:admin') and ->middleware('active') 
but these are never registered. 

Register them:
- 'active' => App\Http\Middleware\EnsureAccountIsActive::class
- 'role' => App\Http\Middleware\RoleMiddleware::class

Check if app/Http/Middleware/RoleMiddleware.php exists. If it does not exist, create it.
The RoleMiddleware should:
- Accept a $role parameter ('admin' or 'super_admin')
- Check $request->user()->role against: 0=user, 1=admin, 2=super_admin
- Redirect to /dashboard with an error flash if role doesn't match

--- BUG 4: PaystackWebhookController method name mismatch ---
File: app/Http/Controllers/PaystackWebhookController.php and routes/web.php

The route calls [PaystackWebhookController::class, 'handleEvent']
but the controller method is named handle(), not handleEvent().
Fix: Either rename the method to handleEvent() in the controller,
OR update the route to call 'handle'. Use handleEvent() for clarity.

--- BUG 5: Wallet table name inconsistency ---
File: app/Models/Wallet.php

The model sets protected $table = 'wallet' (singular).
Check database/migrations/2026_03_07_091713_create_wallet_table.php to confirm 
the actual table name in the migration. Make the model match the migration exactly.
Do not rename the table — just make model and migration consistent.

--- BUG 6: public/hot file ---
Delete the file public/hot if it exists. 
This leftover Vite dev server file causes production assets to load from localhost:5173,
breaking the site for anyone who isn't running npm run dev.
Add public/hot to .gitignore.

--- BUG 7: TransferService missing transaction_type field ---
File: app/Services/TransferService.php

The Transaction::create() calls use 'transaction_type' as the field name,
but app/Models/Transaction.php has 'type' in its $fillable array.
Check the transactions migration to find the real column name.
Make TransferService and Transaction model consistent — use whatever the migration defines.

After all fixes, run: php artisan route:list
Show me the output so we can confirm no duplicate routes exist.
```

---

## SECTION 3 — Environment & Herd Setup (Paste next)

```
Set up the .env file and config for CarePay to work properly with Laravel Herd and Expose.

First, ask me for the following — do NOT proceed until I give you the answers:
1. What is your current Laravel Herd Expose URL? (e.g. https://etinby0cxb.sharedwithexpose.com)
   Or type 'local' if you're only running locally on http://localhost or a .test domain.
2. Your MySQL database name, username, and password (or confirm if using the existing .env values)
3. Your Paystack PUBLIC key (starts with pk_)
4. Your Paystack SECRET key (starts with sk_)
5. Your Mailtrap username and password (for email testing) — or say 'skip' to use log driver
6. Do you have Twilio credentials for SMS OTP? (yes/no — say no to use email OTP only)

Once I provide the answers, update .env with these rules:
- APP_URL = the Expose URL (or local URL)
- APP_ASSET_URL = same as APP_URL
- SANCTUM_STATEFUL_DOMAINS = add the Expose domain (strip https://) to the existing list
- SESSION_DOMAIN = the Expose domain (strip https://)
- DB_CONNECTION = mysql (keep existing unless I said otherwise)
- PAYSTACK_PUBLIC_KEY and PAYSTACK_SECRET_KEY = my provided keys
- PAYSTACK_PAYMENT_URL = https://api.paystack.co
- If Mailtrap: MAIL_MAILER=smtp, MAIL_HOST=smtp.mailtrap.io, MAIL_PORT=2525, fill credentials
- If skip mail: MAIL_MAILER=log
- If no Twilio: comment out TWILIO_* lines

Then update config/services.php to ensure:
    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'url'    => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    ],

Then run: php artisan config:clear && php artisan cache:clear
Show me confirmation it worked.
```

---

## SECTION 4 — Backend: Complete the Core Features

```
Complete the missing backend logic in CarePay. Keep all existing code — only add what's missing.

--- TASK 1: Enforce transaction limits in SendMoney ---
File: app/Livewire/SendMoney.php

Read the file first. Then add limit enforcement to the transfer() or submit() method:
- Load the sender's UserLimit record: $sender->limits
- If no limits record exists, create default (100,000 single / 500,000 daily)
- Before processing: check $amount > $limits->single_transaction_limit → throw validation error
- Check today's total outgoing transactions for this user from the Transaction model
  (sum of amounts where user_id = sender, type = transfer/debit, date = today)
  If sum + $amount > $limits->daily_transfer_limit → throw validation error  
- After successful transfer: do NOT reset the limit, it resets daily via the existing limit_reset_date logic
- Show the user their remaining daily limit on the send money form

--- TASK 2: KYC document upload in AdminKYC ---
Files: app/Livewire/Admin/AdminKYC.php and resources/views/livewire/admin/admin-kyc.blade.php

Read both files first. Then:
- Add a Livewire file upload property: public $kycDocument
- Add an approveKyc($userId) method that sets kyc_verified = true on the User
- Add a rejectKyc($userId, $reason) method that sets kyc_verified = false and 
  stores rejection reason (add a kyc_rejection_reason column via migration if it doesn't exist)
- In the blade view, show each pending KYC user's submitted id_document (if it's a file path, 
  show a link/thumbnail), with Approve and Reject buttons
- When approved/rejected, send the user a notification (use existing Notification pattern in the app)

--- TASK 3: Complete the Paystack deposit flow ---
File: app/Livewire/AddMoney.php and app/Http/Controllers/DepositController.php

Read both files. Then ensure:
- The "Card" deposit step calls PaymentService::initialize() with correct params
- The reference is stored before redirect so the callback can match it
- The /payment/callback route verifies the transaction with Paystack API
  (GET https://api.paystack.co/transaction/verify/:reference) before crediting the wallet
- After verification, credit the wallet and create a Transaction record
- Redirect to dashboard with success message

Ask me: "Should card deposits go through Paystack Checkout (redirect) or inline JS widget?"
Wait for my answer before writing the deposit initialization code.

--- TASK 4: Audit logging ---
File: app/Models/AuditLog.php and app/Services/TransferService.php

Read AuditLog model. Then add AuditLog::create() calls in TransferService after every 
successful transfer, logging: user_id, action='transfer', metadata (amount, recipient, reference).
Add the same in PaystackWebhookController after successful deposit credit.

--- TASK 5: Scheduled payments console command ---
File: app/Console/Commands/ProcessScheduledPayment.php

Read the file. If it exists but is incomplete, finish it:
- Query ScheduledPayment where scheduled_date <= today and status = 'pending'
- For each, call TransferService::transfer() 
- Mark as 'completed' or 'failed'
- Schedule it in routes/console.php to run daily: Schedule::command('payments:process')->daily()
```

---

## SECTION 5 — Frontend: Polish Every Page

```
Improve the frontend of every CarePay page. Keep the dark purple glassmorphism theme.
Keep all existing Blade components. Only enhance — do not remove anything working.

--- TASK 1: Real spending chart on Dashboard ---
File: resources/views/livewire/dashboard-page.blade.php
File: app/Livewire/DashboardPage.php

Read both files. The chart area currently shows a placeholder box.

In DashboardPage.php, add a getChartData() method that returns the last 6 months of:
- Monthly income (sum of 'received' type transactions)  
- Monthly expenses (sum of 'transfer'/'debit' type transactions)
Format: ['labels' => ['Dec', 'Jan', ...], 'income' => [...], 'expenses' => [...]]
Pass this as a $chartData public property (JSON encoded).

In the blade view, replace the placeholder div with a real Chart.js line/bar chart.
Load Chart.js from CDN: https://cdn.jsdelivr.net/npm/chart.js
The chart should match the app's color scheme:
- Income line: #a855f7 (purple)
- Expenses line: #c084fc (lighter purple)  
- Grid lines: rgba(255,255,255,0.05)
- Background: transparent
- Labels: white, small font

--- TASK 2: Mobile navigation ---
File: resources/views/components/layouts/app.blade.php

Read the file. Add a fixed bottom navigation bar for mobile (visible only on xs/sm screens via Bootstrap d-md-none):
Icons and labels for: Dashboard, Send, Add Money, Transactions, Profile
Each item should use a Lucide icon above a small label.
Active state: purple icon + purple text. Inactive: muted gray.
Style it with glassmorphism (backdrop-filter: blur) matching the rest of the app.
Add padding-bottom to the main content area on mobile so it doesn't hide behind the nav bar.

--- TASK 3: Toast notifications ---
File: resources/views/components/ui/toast-container.blade.php
File: resources/views/components/layouts/app.blade.php

Read both. Implement Livewire event-based toast notifications:
- When Livewire dispatches a 'toast' event with {type: 'success'|'error'|'info', message: '...'}
  show a toast in the top-right corner
- Success: green border. Error: red border. Info: purple border.
- Auto-dismiss after 4 seconds with fade animation
- Make sure app.blade.php includes the toast container
- Add dispatch('toast', ['type' => 'success', 'message' => 'Transfer successful!']) 
  to TransferService completion in SendMoney Livewire component

--- TASK 4: Empty states ---
In these blade views, add a proper empty state (icon + heading + CTA button) when there's no data:
- resources/views/livewire/transactions.blade.php — "No transactions yet. Send or receive money to get started." + link to send-money
- resources/views/livewire/wallet.blade.php — "Your wallet is empty. Add money to get started." + link to add-money
- resources/views/livewire/dashboard-page.blade.php — if $recentTransactions is empty, show a friendly empty state instead of nothing

--- TASK 5: Loading states ---
In these Livewire blade views, add wire:loading spinners on all action buttons 
(transfer submit, add money submit, bill payment submit):
- Use: <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm"></span>
- Disable the button during loading: wire:loading.attr="disabled"
- Keep the button text visible when not loading using wire:loading.remove on a span

--- TASK 6: Form validation display ---
In register.blade.php and login.blade.php Livewire views:
- Read the files first
- Ensure @error('fieldname') is used under every input field
- Style errors consistently: small red text, and add 'is-invalid' Bootstrap class to the input
- Add a global error alert at the top if there are any validation errors

--- TASK 7: Landing page improvements ---
File: resources/views/livewire/frontpages.blade.php

Read the file. Then make these enhancements:
1. The hero section mock balance card (currently static ₦12,450.00) — add a subtle CSS 
   count-up animation on page load using a small inline script (vanilla JS, no jQuery)
2. Add a "How it Works" section between Features and Security with 3 steps:
   Step 1: Create Account — Step 2: Add Money — Step 3: Send & Pay
   Use numbered cards with Lucide icons, same card-luxury styling
3. Add a statistics bar: "10,000+ Users" | "₦500M+ Transferred" | "99.9% Uptime"
   Style it as a full-width dark band between sections
4. In the footer, add actual social media icon links (Twitter/X, Instagram, LinkedIn) 
   using Lucide icons (lucide-twitter, lucide-instagram, lucide-linkedin)
```

---

## SECTION 6 — Security Hardening

```
Harden CarePay's security. Read each relevant file before making changes.

--- TASK 1: Remove test route ---
File: routes/web.php

Remove this route entirely — it exposes raw user data:
Route::get('/test', function () { return User::first(); });

--- TASK 2: PIN hashing ---
File: app/Services/PinService.php and app/Models/User.php

Read PinService.php. Check if PINs are being stored as plain text or hashed.
If plain text: update PinService to hash PINs using bcrypt() before storing,
and update PIN verification to use Hash::check() instead of direct comparison.
Add 'pin' => 'hashed' to the User model casts array.

--- TASK 3: Rate limiting ---
File: routes/web.php or app/Http/Middleware/

Add rate limiting to sensitive routes using Laravel's built-in throttle middleware:
- Login: ->middleware('throttle:5,1') (5 attempts per minute)
- Register: ->middleware('throttle:3,1')  
- /webhook/paystack: ->middleware('throttle:60,1')

--- TASK 4: Webhook security ---
File: app/Http/Controllers/PaystackWebhookController.php

The webhook currently verifies signature inline but also dispatches a job.
Ensure the signature verification happens BEFORE dispatching the job.
Add a check: if the request IP is not from Paystack's IP range, log a warning.
Paystack webhook IPs: 52.31.139.75, 52.49.173.169, 52.214.14.220

--- TASK 5: Encrypted fields consistency ---
File: app/Models/User.php

Confirm that 'phone' and 'id_number' are cast with Encrypted::class.
In TransferService, the recipient lookup is:
User::where('phone', $recipientPhone)->first()
This WON'T work with encrypted phone numbers. Fix the lookup to use 
username or email instead of phone number for recipient search.
Update the SendMoney Livewire component's UI to ask for username or email, not phone.
Ask me: "Do you want users to send money by username, email, or phone number?"
Wait for my answer before changing the lookup field.
```

---

## SECTION 7 — Admin Panel Completion

```
Complete the admin panel. Read every admin file before changing anything.

Files to read first:
- app/Livewire/Admin/Admin.php
- app/Livewire/Admin/AdminUsers.php  
- app/Livewire/Admin/AdminTransactions.php
- app/Livewire/Admin/AdminKYC.php
- All corresponding blade views in resources/views/livewire/admin/

--- TASK 1: Admin dashboard stats ---
In Admin.php, add these computed properties:
- Total users count
- Total transaction volume (sum of all completed transactions)
- Pending KYC count (users where kyc_verified = false and id_document is not null)
- Today's transaction count and volume
Show these as stat cards in the admin dashboard blade view using the same card-luxury styling.

--- TASK 2: User management ---
In AdminUsers.php, ensure these actions work:
- View user details (name, email, phone, role, wallet balance, kyc status)
- Suspend/activate user (toggle status between 'active' and 'suspended')
- Change user role (0=user, 1=admin, 2=super_admin) — only super_admin should see this option
- Search users by name, email, or username
- Pagination (15 per page)

--- TASK 3: Transaction oversight ---
In AdminTransactions.php:
- List all transactions with: user name, type, amount, status, reference, date
- Filter by: type (transfer/deposit/bill), status (pending/completed/failed), date range
- Search by reference number
- Export to CSV button (use Laravel's built-in response()->streamDownload())
- Pagination (20 per page)

--- TASK 4: Admin layout sidebar ---
File: resources/views/components/layouts/admin.blade.php

Read the file. Ensure the admin sidebar has:
- CarePay logo at top
- Nav items: Dashboard, Users, Transactions, KYC, (Settings if super_admin)
- Active state highlighting (compare current route with route('admin.xxx'))
- Logout button at the bottom
- Collapsible on mobile using Bootstrap offcanvas
```

---

## SECTION 8 — Performance & Production Readiness

```
Prepare CarePay for production. Do each task carefully.

--- TASK 1: Database indexes ---
Create a new migration: php artisan make:migration add_performance_indexes

Add indexes:
- transactions table: index on (user_id, created_at), index on (reference), index on (status)
- wallet table: index on (user_id)
- users table: index on (email), index on (username) if not already unique

--- TASK 2: Eager loading ---
Find all Livewire components that load users with wallets or transactions.
Replace any N+1 queries with eager loading:
- User::with('wallet') instead of loading wallet in a loop
- Transaction::with('user', 'recipient') for transaction lists
Check: AdminUsers.php, AdminTransactions.php, DashboardPage.php, Transactions.php

--- TASK 3: Vite production config ---
File: vite.config.js

Read the file. Ensure it has:
- input: ['resources/css/app.css', 'resources/css/bootstrap.css', 'resources/css/custom.css', 'resources/js/app.js']
- Proper public base path configuration

Then create a build checklist comment at the top of the file explaining:
"Run `npm run build` before deploying. Delete public/hot after build."

--- TASK 4: Queue configuration for Herd ---
File: README.md (create or update)

Add a "Running Locally with Herd" section that explains:
1. Start Herd site for this project
2. Run: php artisan horizon (in a separate terminal — Horizon manages the queue workers)
3. Run: npm run dev (for Vite hot reload)
4. For Expose sharing: herd share, then update APP_URL and SANCTUM_STATEFUL_DOMAINS in .env
5. Database: php artisan migrate --seed

--- TASK 5: Error pages ---
Create custom error pages:
- resources/views/errors/404.blade.php — "Page not found" with link back to dashboard
- resources/views/errors/500.blade.php — "Something went wrong" with support contact
Both should use the same dark purple theme and @extends nothing (standalone HTML with Vite assets).

--- TASK 6: Final check ---
Run these commands and show me the output:
1. php artisan route:list --compact
2. php artisan config:show database
3. php artisan migrate:status
4. php artisan about

Flag anything that looks wrong.
```

---

## SECTION 9 — Bill Payment Feature Completion

```
Complete the bill payment feature. 

Read these files first:
- app/Livewire/BillPayment.php
- resources/views/livewire/bill-payment.blade.php

CarePay should support Nigerian bill payments:
- Airtime top-up (any network: MTN, Airtel, Glo, 9mobile)
- Data bundles
- Electricity (prepaid token)
- Cable TV (DSTV, GOtv)

Ask me: "Do you have a VTPass API key or Flutterwave for bill payments, or should I mock the bill payment flow for now?"

Wait for my answer. Then:

If VTPass: Integrate VTPass API (https://vtpass.com/documentation)
- Create app/Services/BillPaymentService.php
- Methods: buyAirtime($phone, $network, $amount), buyData($phone, $network, $plan), 
  payElectricity($meterNumber, $disco, $amount, $type), payCable($smartcard, $provider, $plan)
- Debit the user's wallet before calling VTPass
- If VTPass fails, refund the wallet and show error
- Log all bill payment attempts in the transactions table with type='bill_payment'

If mock: Create a realistic mock that:
- Validates the inputs (phone format, meter number format)
- Deducts from wallet
- Creates a transaction record with type='bill_payment'  
- Shows a "success" screen with a fake token/confirmation number
- Clearly marks as mock in code comments for easy replacement later

In the blade view:
- Step 1: Category selection (Airtime / Data / Electricity / Cable) — use icon cards
- Step 2: Provider selection (network logos or names as button group)
- Step 3: Details form (varies by category)
- Step 4: Confirm (show amount, recipient, provider)
- Step 5: PIN verification before processing
- Step 6: Success screen with receipt details

Keep the multi-step pattern consistent with the existing AddMoney and SendMoney flows.
```

---

## SECTION 10 — Final Integration Test

```
Run a full integration test of CarePay. 

1. Check that all migrations run cleanly:
   php artisan migrate:fresh --seed
   If there are no seeders, create a basic DatabaseSeeder that creates:
   - 1 admin user (role=1, kyc_verified=true, email=admin@carepay.test, password=password)
   - 1 regular user (role=0, kyc_verified=true, email=user@carepay.test, password=password)
   - Give each user a wallet with 50,000 NGN balance (in kobo: 5,000,000)

2. Test these flows manually and fix any errors you find:
   - Register a new user → email verification → login
   - Dashboard loads with balance, stats, chart
   - Send money from user to another user
   - Add money via Paystack (use test mode)
   - View transactions list
   - Admin login → view users, transactions, KYC

3. Run: php artisan test
   If there are no tests, create basic smoke tests in tests/Feature/:
   - GuestCanViewHomePage
   - UserCanLogin  
   - UserCanViewDashboardWhenAuthenticated
   - GuestIsRedirectedFromDashboard
   - TransferDeductsSenderAndCreditRecipient (use RefreshDatabase)

4. Generate the IDE helper files for better autocompletion:
   php artisan ide-helper:generate
   php artisan ide-helper:models --nowrite

5. Final .env reminder — show me a checklist of every .env value that needs to be 
   updated before going live (mark each as REQUIRED or OPTIONAL).
```

---

## Quick Reference — Key Files

| What | Where |
|------|-------|
| User model | `app/Models/User.php` |
| Wallet model | `app/Models/Wallet.php` |
| Transaction model | `app/Models/Transaction.php` |
| Transfer logic | `app/Services/TransferService.php` |
| Paystack payments | `app/Services/PaymentServices.php` (class is `PaymentService`) |
| Webhook handler | `app/Http/Controllers/PaystackWebhookController.php` |
| Dashboard Livewire | `app/Livewire/DashboardPage.php` |
| Send Money Livewire | `app/Livewire/SendMoney.php` |
| Add Money Livewire | `app/Livewire/AddMoney.php` |
| App layout | `resources/views/components/layouts/app.blade.php` |
| Admin layout | `resources/views/components/layouts/admin.blade.php` |
| Custom CSS | `resources/css/custom.css` |
| Routes | `routes/web.php` |
| Middleware registration | `bootstrap/app.php` |
| Services config | `config/services.php` |

---

## Known Issues Summary (for Copilot context)

| Bug | File | Severity |
|-----|------|----------|
| Class body broken, methods outside class | `app/Models/User.php` | 🔴 Fatal |
| Duplicate /dashboard and /payment/callback routes | `routes/web.php` | 🔴 Fatal |
| 'role' and 'active' middleware not registered | `bootstrap/app.php` | 🔴 Fatal |
| Controller method name mismatch (handle vs handleEvent) | `PaystackWebhookController.php` | 🔴 Fatal |
| GET logout (CSRF vulnerability) | `routes/web.php` | 🟠 Security |
| PIN stored as plain text | `PinService.php` | 🟠 Security |
| Test route exposes user data | `routes/web.php` | 🟠 Security |
| Encrypted phone breaks recipient lookup | `TransferService.php` | 🟠 Logic |
| Chart is a placeholder div | `dashboard-page.blade.php` | 🟡 UX |
| No mobile bottom nav | `app.blade.php` | 🟡 UX |
| No transaction limit enforcement | `SendMoney.php` | 🟡 Logic |
| KYC approve/reject not wired | `AdminKYC.php` | 🟡 Incomplete |
| public/hot file left in repo | `public/hot` | 🟡 Deploy |
| N+1 queries in admin lists | Multiple Livewire files | 🟢 Performance |
