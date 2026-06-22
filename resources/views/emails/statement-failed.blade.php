<x-mail::message>
# ⚠️ Statement Export Issue

Hello **{{ $user->first_name }}**,

Unfortunately, we encountered an issue processing your statement of account export. Our team has been notified and is working to resolve this.

**Request Details:**
- **Period:** {{ $startDate->format('F j, Y') }} - {{ $endDate->format('F j, Y') }}
- **Error Code:** Technical issue (ID: {{ uniqid() }})
- **Requested:** {{ now()->format('F j, Y \a\t h:i A') }}

---

**What you can do:**

1. **Try Again:** Request the statement again from your CarePay dashboard.
2. **Contact Support:** If the issue persists, our support team is ready to assist.

<x-mail::button :url="config('app.url') . '/dashboard'">
📊 Go to Dashboard
</x-mail::button>

---

**Need immediate help?** Reply to this email or email support@carepay.com

We apologize for the inconvenience and appreciate your patience.

Best regards,

**CarePay Team** 🏦

@component('mail::subcopy')
For security, never share your account information via email. CarePay will never ask for your password.
@endcomponent
</x-mail::message>
