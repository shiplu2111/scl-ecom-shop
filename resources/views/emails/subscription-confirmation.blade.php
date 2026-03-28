<x-mail::message>
# Subscription Confirmed

Hello,

Thank you for subscribing to our newsletter! You'll now receive the latest updates, offers, and news from {{ config('app.name') }} directly in your inbox ({{ $email }}).

If you didn't mean to subscribe, you can safely ignore this email or unsubscribe at any time.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
