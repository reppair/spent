<flux:card class="overflow-hidden min-w-[12rem]">
    <flux:text>{{ __('By Group') }}</flux:text>

    @if($this->stats->isEmpty())
        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('No expenses in selected group(s) and period') }}
        </flux:text>
    @else
        <ul class="mt-3 space-y-2">
            @foreach($this->stats as $stat)
                <li>
                    <!-- Group row -->
                    <div class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <!-- Background fill representing percentage -->
                        <div
                            class="absolute inset-0 bg-sky-100 dark:bg-sky-700/30 transition-all"
                            style="width: {{ $stat->percentage }}%"
                        ></div>

                        <!-- Content -->
                        <div class="relative flex items-center justify-between gap-3 px-3 py-1 text-sm">
                            <span class="text-zinc-900 dark:text-zinc-100">
                                {{ $stat->name }}
                            </span>
                            <div class="flex items-center gap-2 tabular-nums">
                                <span class="text-zinc-900 dark:text-zinc-100">
                                    {{ $stat->formatted_amount }}
                                </span>
                                <span class="text-zinc-500 dark:text-zinc-400 w-10 text-right">
                                    ({{ $stat->percentage }}%)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Nested user rows -->
                    @if($stat->users->isNotEmpty())
                        <ul class="mt-1 space-y-1">
                            @foreach($stat->users as $user)
                                <li class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 ml-4">
                                    <!-- Background fill representing percentage within group -->
                                    <div
                                        class="absolute inset-0 bg-sky-50 dark:bg-sky-700/20 transition-all"
                                        style="width: {{ $user->percentage }}%"
                                    ></div>

                                    <!-- Content -->
                                    <div class="relative flex items-center justify-between gap-3 px-2 py-0.5 text-xs">
                                        <span class="text-zinc-900 dark:text-zinc-100">
                                            {{ $user->name }}
                                        </span>
                                        <div class="flex items-center gap-2 tabular-nums">
                                            <span class="text-zinc-900 dark:text-zinc-100">
                                                {{ $user->formatted_amount }}
                                            </span>
                                            <span class="text-zinc-500 dark:text-zinc-400 w-8 text-right">
                                                ({{ $user->percentage }}%)
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</flux:card>
