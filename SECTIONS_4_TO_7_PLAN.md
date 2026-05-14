# SECTIONS 4-7 — Feature Completion Plan

## Overview

These sections complete the core backend features, frontend polish, security hardening, and admin panel for MyFintechApp. Work through them **one section at a time**, in order.

---

# SECTION 4 — Backend: Core Features

## TASK 4.1: Transaction Limits Enforcement

**File:** `app/Livewire/SendMoney.php`

**What needs to be done:**
1. Load the sender's UserLimit record
2. Before transfer, check single transaction limit (default 100,000 NGN)
3. Check daily transfer limit (default 500,000 NGN)
4. Show user their remaining daily limit on the form
5. Prevent transfer if limit exceeded with validation error

**Key logic:**
```php
// In SendMoney transfer/submit method:
$limit = $user->limit; // or create default
if ($amount > $limit->single_transaction_limit) {
    throw ValidationException('Exceeds single transfer limit');
}
// Check today's total transfers
$todayTotal = Transaction::where('user_id', $user->id)
    ->where('type', 'transfer')
    ->whereDate('created_at', today())
    ->sum('amount');
if ($todayTotal + $amount > $limit->daily_transfer_limit) {
    throw ValidationException('Exceeds daily transfer limit');
}
```

**Status:** ⏳ Not yet started

---

## TASK 4.2: KYC Document Upload & Approval

**Files:**
- `app/Livewire/Admin/AdminKYC.php`
- `resources/views/livewire/admin/admin-kyc.blade.php`

**What needs to be done:**
1. Add Livewire file upload property for KYC documents
2. Create `approveKyc($userId)` method → sets `kyc_verified = true`
3. Create `rejectKyc($userId, $reason)` method → sets `kyc_verified = false` + stores reason
4. Create migration to add `kyc_rejection_reason` column if needed
5. Display each pending KYC user with:
   - Their ID document (link/thumbnail)
   - Approve & Reject buttons
6. Send notification to user when approved/rejected

**Status:** ⏳ Not yet started

---

## TASK 4.3: Paystack Deposit Flow

**Files:**
- `app/Livewire/AddMoney.php`
- `app/Http/Controllers/DepositController.php`

**What needs to be done:**
1. Ensure "Card" deposit step calls `PaymentService::initialize()` correctly
2. Store transaction reference before Paystack redirect
3. `/payment/callback` route verifies with Paystack API:
   ```
   GET https://api.paystack.co/transaction/verify/:reference
   ```
4. After verification, credit wallet and create Transaction record
5. Redirect to dashboard with success message

**Key decision needed from you:**
- Should card deposits use Paystack Checkout (redirect) or inline JS widget?

**Status:** ⏳ Blocked - awaiting your deposit method preference

---

## TASK 4.4: Audit Logging

**Files:**
- `app/Models/AuditLog.php`
- `app/Services/TransferService.php`
- `app/Http/Controllers/PaystackWebhookController.php`

**What needs to be done:**
1. Add `AuditLog::create()` calls after successful transfers in TransferService
2. Log: user_id, action='transfer', metadata (amount, recipient, reference)
3. Add same logging to PaystackWebhookController after deposit credit
4. Use existing AuditLog pattern from MyFintechApp fixes

**Status:** ⏳ Not yet started

---

## TASK 4.5: Scheduled Payments Command

**File:** `app/Console/Commands/ProcessScheduledPayment.php`

**What needs to be done:**
1. Read the file (check if it exists and is incomplete)
2. Query ScheduledPayment where scheduled_date ≤ today and status = 'pending'
3. For each: call `TransferService::transfer()`
4. Mark result as 'completed' or 'failed'
5. Register in `routes/console.php`: `Schedule::command('payments:process')->daily()`

**Status:** ⏳ Not yet started

---

## SECTION 4 Summary

| Task | Estimate | Depends On |
|------|----------|-----------|
| 4.1 Transaction Limits | 30 min | UserLimit model (done) |
| 4.2 KYC Upload & Approval | 1 hour | Livewire file upload |
| 4.3 Paystack Deposit Flow | 1 hour | Your decision on deposit method |
| 4.4 Audit Logging | 30 min | AuditLog model (done) |
| 4.5 Scheduled Payments | 45 min | ScheduledPayment model |

**Total Estimate:** 3.5 hours

---

# SECTION 5 — Frontend: Polish Every Page

## TASK 5.1: Real Spending Chart on Dashboard

**Files:**
- `app/Livewire/DashboardPage.php`
- `resources/views/livewire/dashboard-page.blade.php`

**What needs to be done:**
1. In DashboardPage, add `getChartData()` method returning last 6 months:
   ```php
   [
       'labels' => ['Dec', 'Jan', 'Feb', ...],
       'income' => [sum of 'received' transactions per month],
       'expenses' => [sum of 'transfer' transactions per month]
   ]
   ```
2. Pass as `$chartData` JSON property
3. Replace placeholder div with real Chart.js chart
4. Load Chart.js from CDN: https://cdn.jsdelivr.net/npm/chart.js
5. Style colors:
   - Income: #a855f7 (purple)
   - Expenses: #c084fc (lighter purple)
   - Grid: rgba(255,255,255,0.05)

**Status:** ⏳ Not yet started

---

## TASK 5.2: Mobile Navigation

**File:** `resources/views/components/layouts/app.blade.php`

**What needs to be done:**
1. Add fixed bottom nav bar (mobile only: `d-md-none`)
2. Navigation items: Dashboard, Send, Add Money, Transactions, Profile
3. Lucide icons above labels
4. Active state: purple. Inactive: gray
5. Glassmorphism styling (backdrop-filter: blur)
6. Add padding-bottom to main content so content doesn't hide behind nav

**Status:** ⏳ Not yet started

---

## TASK 5.3: Toast Notifications

**Files:**
- `resources/views/components/ui/toast-container.blade.php`
- `resources/views/components/layouts/app.blade.php`

**What needs to be done:**
1. Implement toast system that listens to Livewire 'toast' event:
   ```php
   dispatch('toast', ['type' => 'success', 'message' => 'Transfer successful!'])
   ```
2. Toast types: success (green), error (red), info (purple)
3. Position: top-right corner
4. Auto-dismiss after 4 seconds with fade animation
5. Include toast container in app.blade.php
6. Add dispatch() calls to TransferService success flows

**Status:** ⏳ Not yet started

---

## TASK 5.4: Empty States

**Files:**
- `resources/views/livewire/transactions.blade.php`
- `resources/views/livewire/wallet.blade.php`
- `resources/views/livewire/dashboard-page.blade.php`

**What needs to be done:**
1. **Transactions:** "No transactions yet" → link to send-money
2. **Wallet:** "Wallet is empty" → link to add-money
3. **Dashboard:** Empty recent transactions → friendly empty state
4. Each should have: icon + heading + CTA button

**Status:** ⏳ Not yet started

---

## TASK 5.5: Loading States

**Files:**
- `resources/views/livewire/send-money.blade.php`
- `resources/views/livewire/add-money.blade.php`
- `resources/views/livewire/bill-payment.blade.php`

**What needs to be done:**
1. Add spinner on all action buttons during form submission:
   ```blade
   <span wire:loading wire:target="submit" class="spinner-border spinner-border-sm"></span>
   ```
2. Disable button: `wire:loading.attr="disabled"`
3. Keep text visible with `wire:loading.remove` on text span

**Status:** ⏳ Not yet started

---

## TASK 5.6: Form Validation Display

**Files:**
- `resources/views/livewire/register.blade.php`
- `resources/views/livewire/login.blade.php`

**What needs to be done:**
1. Ensure `@error('fieldname')` under every input
2. Style: small red text, add 'is-invalid' Bootstrap class to input
3. Add global error alert at top if any validation errors exist

**Status:** ⏳ Not yet started

---

## TASK 5.7: Landing Page Improvements

**File:** `resources/views/livewire/frontpages.blade.php`

**What needs to be done:**
1. Add count-up animation to hero card balance (vanilla JS, no jQuery)
2. Add "How it Works" section with 3 numbered steps + Lucide icons
3. Add statistics bar: "10,000+ Users | ₦500M+ Transferred | 99.9% Uptime"
4. Add social media icons (Twitter/X, Instagram, LinkedIn) in footer with Lucide icons

**Status:** ⏳ Not yet started

---

## SECTION 5 Summary

| Task | Estimate | Depends On |
|------|----------|-----------|
| 5.1 Chart | 45 min | Chart.js + Transaction data |
| 5.2 Mobile Nav | 1 hour | Bootstrap responsive design |
| 5.3 Toasts | 45 min | Livewire events |
| 5.4 Empty States | 30 min | Each page |
| 5.5 Loading States | 30 min | Wire:loading directive |
| 5.6 Form Validation | 30 min | Bootstrap validation |
| 5.7 Landing Page | 1 hour | CSS animations |

**Total Estimate:** 5 hours

---

# SECTION 6 — Security Hardening

## TASK 6.1: Remove Test Route

**File:** `routes/web.php`

**What needs to be done:**
1. Delete this entire route:
   ```php
   Route::get('/test', function () { return User::first(); });
   ```

**Status:** ⏳ Not yet started

---

## TASK 6.2: PIN Hashing

**Files:**
- `app/Services/PinService.php`
- `app/Models/User.php`

**What needs to be done:**
1. Check if PINs are stored as plain text
2. If yes: hash with `bcrypt()` before storing
3. Verify with `Hash::check()` instead of direct comparison
4. Add `'pin' => 'hashed'` to User model casts

**Status:** ⏳ Not yet started

---

## TASK 6.3: Rate Limiting

**File:** `routes/web.php`

**What needs to be done:**
1. Add throttle middleware to sensitive routes:
   ```php
   Route::post('/login', ...)->middleware('throttle:5,1');
   Route::post('/register', ...)->middleware('throttle:3,1');
   Route::post('/webhook/paystack', ...)->middleware('throttle:60,1');
   ```
   (5 attempts per 1 minute for login, etc.)

**Status:** ⏳ Not yet started

---

## TASK 6.4: Webhook Security

**File:** `app/Http/Controllers/PaystackWebhookController.php`

**What needs to be done:**
1. Ensure signature verification happens BEFORE dispatching job
2. Add IP whitelist check for Paystack IPs:
   - 52.31.139.75
   - 52.49.173.169
   - 52.214.14.220
3. Log warning if request from unknown IP

**Status:** ⏳ Not yet started

---

## TASK 6.5: Recipient Lookup Fix

**Files:**
- `app/Models/User.php`
- `app/Services/TransferService.php`
- `app/Livewire/SendMoney.php`

**What needs to be done:**
1. **Key Decision Needed from You:**
   - Should users send money by: username, email, or phone number?
2. Currently: TransferService uses `User::where('phone', ...)->first()`
3. But phone is encrypted, so lookup fails
4. Choose ONE: username lookup OR email lookup OR phone lookup with decryption
5. Update TransferService query accordingly
6. Update SendMoney UI to ask for the chosen field (username/email/phone)

**Status:** ⏳ Blocked - awaiting your recipient lookup preference

---

## SECTION 6 Summary

| Task | Estimate | Depends On |
|------|----------|-----------|
| 6.1 Remove Test Route | 5 min | File access |
| 6.2 PIN Hashing | 30 min | Hash review |
| 6.3 Rate Limiting | 20 min | Route structure |
| 6.4 Webhook Security | 30 min | Paystack API docs |
| 6.5 Recipient Lookup | 45 min | Your field choice |

**Total Estimate:** 2 hours 10 min

---

# SECTION 7 — Admin Panel Completion

## TASK 7.1: Admin Dashboard Stats

**File:** `app/Livewire/Admin/Admin.php`

**What needs to be done:**
1. Add computed properties:
   - Total users count
   - Total transaction volume (sum of all completed amounts)
   - Pending KYC count (kyc_verified = false)
   - Today's transaction count + volume
2. Display as stat cards in blade view using card-luxury styling

**Status:** ⏳ Not yet started

---

## TASK 7.2: User Management

**File:** `app/Livewire/Admin/AdminUsers.php`

**What needs to be done:**
1. View user details (name, email, phone, role, wallet balance, KYC status)
2. Suspend/activate user (toggle status)
3. Change user role (0=user, 1=admin, 2=super_admin) — super_admin only
4. Search by name/email/username
5. Pagination (15 per page)

**Status:** ⏳ Not yet started

---

## TASK 7.3: Transaction Oversight

**File:** `app/Livewire/Admin/AdminTransactions.php`

**What needs to be done:**
1. List transactions: user name, type, amount, status, reference, date
2. Filter by: type, status, date range
3. Search by reference number
4. Export to CSV button
5. Pagination (20 per page)

**Status:** ⏳ Not yet started

---

## TASK 7.4: Admin Sidebar Layout

**File:** `resources/views/components/layouts/admin.blade.php`

**What needs to be done:**
1. Add CarePay logo at top
2. Nav items: Dashboard, Users, Transactions, KYC, (Settings if super_admin)
3. Active state highlighting
4. Logout button at bottom
5. Mobile: collapsible offcanvas

**Status:** ⏳ Not yet started

---

## SECTION 7 Summary

| Task | Estimate | Depends On |
|------|----------|-----------|
| 7.1 Dashboard Stats | 45 min | Database queries |
| 7.2 User Management | 1 hour | Livewire + Blade |
| 7.3 Transaction Oversight | 1 hour | Filters + export |
| 7.4 Admin Sidebar | 30 min | Blade components |

**Total Estimate:** 3.5 hours

---

# SECTION 8 — Performance & Production (Not detailed here - comes after 4-7)

**Preview of SECTION 8 tasks:**
- Add database indexes
- Fix N+1 queries with eager loading
- Vite production config
- Queue worker setup for Herd/Render
- Custom error pages (404, 500)
- Final production checklist

---

# SECTION 9 — Bill Payment Feature (Not detailed here)

**Preview of SECTION 9 tasks:**
- Integrate VTPass API OR mock bill payment flow
- Multi-step UI: Category → Provider → Details → Confirm → PIN → Success
- Support: Airtime, Data, Electricity, Cable TV
- Debit wallet and create transaction

---

# SECTION 10 — Final Integration Test (Not detailed here)

**Preview of SECTION 10 tasks:**
- Fresh migration + seeding
- Manual flow testing
- Feature tests
- IDE helper generation
- Final .env checklist before production

---

## Next Steps

When you're ready, I'll proceed with **SECTION 4** starting with Task 4.1 (Transaction Limits).

**Before we start SECTION 4, let me know:**

1. **For TASK 4.3 (Paystack Deposits):**
   - Should card deposits use Paystack Checkout (redirect) or inline JS widget?

2. **For SECTION 6 TASK 6.5 (Recipient Lookup):**
   - Should users send money by: **username**, **email**, or **phone number**?

Once you provide these answers, I'll begin SECTION 4-7 systematically.

---

## Estimated Total Time for Sections 4-7

- SECTION 4: 3.5 hours
- SECTION 5: 5 hours
- SECTION 6: 2 hours 10 min
- SECTION 7: 3.5 hours

**Total: ~14 hours of work**

Shall we proceed?
