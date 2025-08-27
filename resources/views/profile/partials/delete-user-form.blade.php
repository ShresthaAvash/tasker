<section>
    <p class="mt-1 text-sm text-gray-600">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}
    </p>

    <button type="button" class="btn btn-danger mt-3" data-toggle="modal" data-target="#confirm-user-deletion">
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Are you sure you want to delete your account?') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>
                            {{ __('Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mt-3 form-group">
                            <label for="password_delete" class="sr-only">{{ __('Password') }}</label>
                            <input
                                id="password_delete"
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="{{ __('Password') }}"
                            />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>