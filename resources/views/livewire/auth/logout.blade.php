<div x-data="logoutComponent()">
    <!-- Logout Button -->
    <button @click="confirmLogout()"
        class="dropdown-link block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
        :disabled="isLoggingOut">
        <span x-show="!isLoggingOut">{{ __('Logout') }}</span>
        <span x-show="isLoggingOut">{{ __('Loading...') }}</span>
    </button>

    <!-- Confirmation Modal -->
    <div class="modal-overlay-force"
        :class="{ 'modal-visible-force': showConfirmation, 'modal-hidden-force': !showConfirmation }"
        @click="cancelLogout()" x-show="showConfirmation" x-transition>

        <div class="modal-content-force" @click.stop>
            <!-- Modal Header -->
            <div class="modal-header-force">
                <div class="modal-icon-force">
                    <svg style="width: 24px !important; height: 24px !important; color: #dc2626 !important;"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.966-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="modal-text-force">
                    <h3 class="modal-title-force">{{ __('Confirm Logout') }}</h3>
                    <p class="modal-description-force">
                        {{ __('Are you sure you want to log out? You will need to sign in again to access your account.') }}
                    </p>
                </div>
            </div>

            <!-- Modal Actions -->
            <div class="modal-actions-force">
                <button @click="logout()" type="button" class="modal-btn-danger-force" :disabled="isLoggingOut">

                    <!-- Show spinner only when logging out -->
                    <template x-if="isLoggingOut">
                        <span style="display: flex !important; align-items: center !important;">
                            <svg class="spin-force" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle style="opacity: 0.25 !important;" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity: 0.75 !important;" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ __('Logging out...') }}
                        </span>
                    </template>

                    <template x-if="!isLoggingOut">
                        <span>{{ __('Yes, Logout') }}</span>
                    </template>
                </button>

                <button @click="cancelLogout()" type="button" class="modal-btn-secondary-force"
                    :disabled="isLoggingOut">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('logoutComponent', () => ({
            showConfirmation: false,
            isLoggingOut: false,

            confirmLogout() {
                this.isLoggingOut = false;
                this.showConfirmation = true;
            },

            cancelLogout() {
                this.isLoggingOut = false;
                this.showConfirmation = false;
            },

            logout() {
                this.isLoggingOut = true;
                this.$wire.logout().then(() => {
                    this.isLoggingOut = false;
                    this.showConfirmation = false;
                }).catch(() => {
                    this.isLoggingOut = false;
                    this.showConfirmation = false;
                });
            }
        }));
    </script>
@endscript

