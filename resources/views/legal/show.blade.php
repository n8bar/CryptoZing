<x-public-layout :title="$title">
    @push('head')
        <style>
            .legal-doc p { margin: 0 0 1rem; line-height: 1.65; }
            .legal-doc p:last-child { margin-bottom: 0; }
            .legal-doc ul { margin: 0 0 1rem; padding-left: 1.5rem; list-style: disc; }
            .legal-doc li { margin-bottom: 0.375rem; line-height: 1.6; }
            .legal-doc a { text-decoration: underline; text-underline-offset: 2px; }
        </style>
    @endpush

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <div class="legal-doc overflow-hidden bg-white p-6 text-sm text-gray-900 shadow-sm sm:rounded-lg sm:p-8 dark:bg-slate-900/60 dark:text-slate-100 dark:ring-1 dark:ring-white/10">
                {!! $html !!}
            </div>
        </div>
    </div>
</x-public-layout>
