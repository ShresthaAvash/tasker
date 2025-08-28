@component('mail::message')
# New Contact Message Received

You have received a new message from the contact form on your website.

**From:** {{ $contactMessage->first_name }} {{ $contactMessage->last_name }}<br>
**Email:** {{ $contactMessage->email }}<br>
**Company:** {{ $contactMessage->company ?? 'N/A' }}

**Message:**
> {{ $contactMessage->message }}

You can view this message in the super admin panel.

@component('mail::button', ['url' => route('superadmin.messages.index')])
View Messages
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent