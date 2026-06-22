<x-mail::message>
# 💼 Your Statement of Account is Ready

Hello **{{ $user->first_name }}**,

Great news! Your statement of account for the period **{{ \Carbon\Carbon::parse($startDate)->format('F j, Y') }}** to **{{ \Carbon\Carbon::parse($endDate)->format('F j, Y') }}** has been successfully generated and is ready for download.

<x-mail::button :url="$downloadUrl" color="success">
📥 Download Statement
</x-mail::button>

**Statement Details:**
- **Period:** {{ $startDate->format('F j, Y') }} - {{ $endDate->format('F j, Y') }}
- **Generated:** {{ now()->format('F j, Y \a\t h:i A') }}
- **Format:** Excel (.xlsx)
- **File Name:** {{ $fileName }}

---

**Important:** The download link will expire in 30 days. After that, you can generate a new statement from your CarePay dashboard.

Thank you for using CarePay. We're here to help manage your finances securely and efficiently.

Best regards,

**CarePay Team** 🏦

@component('mail::subcopy')
Did not request this? Please reply to this email or contact support@carepay.com immediately.
@endcomponent
</x-mail::message>
