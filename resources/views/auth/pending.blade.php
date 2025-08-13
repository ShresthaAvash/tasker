<x-guest-layout>
    <div class="mb-4 text-lg text-gray-800">
        {{ __('Registration Successful!') }}
    </div>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thank you for subscribing. Your account is currently awaiting activation from an administrator. You will receive an email once your account has been approved. You may now close this page.') }}
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('landing') }}">
            <x-primary-button>
                {{ __('Return to Homepage') }}
            </x-primary-button>
        </a>
    </div>
</x-guest-layout>