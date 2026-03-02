<x-mail::message>
{{-- Header --}}
<div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 24px; border-radius: 8px 8px 0 0; color: white; text-align: center;">
    <h1 style="margin: 0; font-size: 24px; font-weight: 700;">{{ config('app.name') }}</h1>
    <p style="margin: 8px 0 0 0; font-size: 14px; opacity: 0.9;">Important Notification</p>
</div>

<div style="padding: 32px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; color: #333;">
    {{-- Greeting --}}
    <h2 style="color: #1f2937; margin: 0 0 16px 0; font-size: 18px;">Hello {{ $user->name ?? 'User' }},</h2>

    {{-- Title --}}
    <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 16px; margin: 24px 0; border-radius: 4px;">
        <h3 style="color: #10b981; margin: 0; font-size: 16px; font-weight: 600;">{{ $title }}</h3>
    </div>

    {{-- Body --}}
    <div style="line-height: 1.6; color: #4b5563; margin: 24px 0; white-space: pre-wrap;">
        {{ $body }}
    </div>

    {{-- Call-to-Action Button --}}
    @if($actionUrl)
    <div style="text-align: center; margin: 32px 0;">
        <x-mail::button :url="$actionUrl" color="success">
            View Details →
        </x-mail::button>
    </div>
    @endif

    {{-- Footer Notes --}}
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; padding: 16px; border-radius: 6px; margin: 32px 0 0 0; font-size: 13px; color: #6b7280; line-height: 1.5;">
        <p style="margin: 0 0 8px 0;">
            <strong>⏱️ Delivery Time:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
        </p>
        <p style="margin: 0;">
            <strong>👤 Your Account:</strong> @if($user->role) {{ ucfirst($user->role) }} @else User @endif
        </p>
    </div>
</div>

{{-- Divider --}}
<div style="border-top: 2px solid #e5e7eb; margin: 32px 0; height: 0;"></div>

{{-- Closing --}}
<div style="padding: 24px 32px; background: #f9fafb; border-radius: 0 0 8px 8px; text-align: center;">
    <p style="margin: 0 0 12px 0; color: #6b7280; font-size: 13px;">
        If you have any questions or issues, please reach out to our support team.
    </p>
    <p style="margin: 0; color: #9ca3af; font-size: 12px;">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </p>
</div>
</x-mail::message>
