@extends('layouts.quiz')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="glass rounded-3xl p-8 md:p-12 shadow-2xl relative overflow-hidden">
            <!-- Background Decoration -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary-100 rounded-full blur-3xl opacity-50"></div>

            <div class="relative">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">{{ $quiz->title }}</h2>

                @if($quiz->description)
                    <div class="text-gray-600 mb-8 leading-relaxed">
                        {{ $quiz->description }}
                    </div>
                @endif

                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-8">
                    <div class="flex items-center text-indigo-700 font-semibold mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ready to start?
                    </div>
                    <p class="text-indigo-600 text-sm italic">
                        Enter your email below to begin. You can attempt this quiz as many times as you like.
                    </p>
                </div>

                <form action="{{ route('quiz.start', $quiz->slug) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Your Email Address</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 outline-none transition-all text-lg"
                            placeholder="you@example.com">
                        @error('email')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-primary-600 to-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-primary-200 hover:shadow-xl hover:-translate-y-0.5 active:scale-95 transition-all text-lg">
                        Start Challenge
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection