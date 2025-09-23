<div class="max-w-xl w-full card-container rounded-xl shadow-xl p-6 sm:p-7 transform transition-all"
    x-data="{
        rating: @entangle('rating'),
        hoverRating: 0,
        submitted: @entangle('submitted'),
        name: @entangle('name'),
        opinion: @entangle('opinion'),
        getRatingText() {
            const texts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            return texts[this.rating] || '';
        }
    }" x-init="$el.classList.add('fade-in')">

    <div x-show="!submitted">
        <!-- Header Section -->
        <div class="text-center mb-6">
            <div class="mb-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-text mb-2">We'd love your feedback!</h1>
            <p class="text-gray-600 text-base">Help us improve by sharing your thoughts and experience.</p>
            <div class="w-16 h-1 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full mx-auto mt-3">
            </div>
        </div>

        <form wire:submit.prevent="submit" class="space-y-5">
            <!-- Name Field -->
            <div class="form-group slide-in-up">
                <input type="text" id="name" name="name" x-model="name" wire:model="name" placeholder=" "
                    class="input-field w-full px-4 py-3 text-gray-700 bg-white rounded-lg @error('name') border-red-500 @enderror"
                    required>
                <label for="name" class="floating-label">Your Name</label>
                @error('name')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Opinion Field -->
            <div class="form-group slide-in-up" style="animation-delay: 0.1s">
                <textarea id="opinion" name="opinion" rows="4" x-model="opinion" wire:model="opinion" placeholder=" "
                    class="input-field w-full px-4 py-3 text-gray-700 bg-white rounded-lg resize-none @error('opinion') border-red-500 @enderror"
                    required></textarea>
                <label for="opinion" class="floating-label">Tell us what you think...</label>
                @error('opinion')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Rating Section -->
            <div class="slide-in-up" style="animation-delay: 0.2s">
                <label class="block text-base font-semibold text-gray-700 mb-3 text-center">How would you rate
                    your experience?</label>
                <div class="text-center">
                    <div class="flex items-center justify-center space-x-2 sm:space-x-3 mb-3"
                        @mouseleave="hoverRating = 0">
                        <template x-for="star in 5">
                            <div class="star-rating">
                                <svg @mouseenter="hoverRating = star"
                                    @click="rating = star; $wire.set('rating', star); $el.classList.add('bounce-on-click'); setTimeout(() => $el.classList.remove('bounce-on-click'), 300)"
                                    class="w-10 h-10 sm:w-11 sm:h-11 cursor-pointer transition-all duration-200 transform hover:scale-110"
                                    :class="{
                                        'text-yellow-400': hoverRating >= star || rating >= star,
                                        'text-gray-300': hoverRating < star && rating < star,
                                        'drop-shadow-lg': hoverRating >= star || rating >= star
                                    }"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="rating-text h-6" :class="{ 'show': rating > 0 }">
                        <span x-text="getRatingText()" class="text-base font-medium"
                            :class="{
                                'text-red-500': rating <= 2,
                                'text-yellow-500': rating == 3,
                                'text-green-500': rating >= 4
                            }"></span>
                    </div>
                    @error('rating')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center pt-3 slide-in-up" style="animation-delay: 0.3s">
                <button type="submit" class="w-full sm:w-auto btn-primary font-bold py-3 px-8 rounded-lg"
                    :disabled="!name || !opinion || rating === 0"
                    :class="{ 'opacity-50 cursor-not-allowed': !name || !opinion || rating === 0 }"
                    wire:loading.attr="disabled" wire:target="submit">
                    <span class="flex items-center justify-center space-x-2" wire:loading.remove wire:target="submit">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <span>Submit Feedback</span>
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center justify-center space-x-2">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Submitting...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Success Message -->
    <div x-show="submitted" class="text-center fade-in py-6" x-cloak>
        <div class="success-icon mb-5">
            <div class="w-18 h-18 bg-green-100 rounded-full mx-auto flex items-center justify-center mb-4">
                <svg class="w-9 h-9 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-primary-text mb-3">Thank You!</h2>
        <p class="text-gray-600 mb-3">Your feedback has been received successfully.</p>
        <div x-show="name" class="text-primary-accent font-medium mb-4">
            Thank you, <span x-text="name"></span>, for taking the time to share your thoughts!
        </div>

        <!-- Reset Button -->
        <button wire:click="resetForm"
            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition-all">
            Submit Another Feedback
        </button>
    </div>
</div>
