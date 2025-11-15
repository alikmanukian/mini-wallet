<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { index } from '@/routes/transactions';
import type { PaginatedData } from '@/types';
import type { Transaction } from '@/types/transactions';
import { useAutoAnimate } from '@formkit/auto-animate/vue';

interface Props {
    transactions: PaginatedData<Transaction>;
}

defineProps<Props>();

const [transactionsListRef] = useAutoAnimate();

const formatDate = (dateString: string) => {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(dateString));
};
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
    >
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
            Transaction History
        </h2>

        <div v-if="transactions.data.length === 0" class="py-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">No transactions yet</p>
        </div>

        <div v-else ref="transactionsListRef" class="space-y-3">
            <div
                v-for="transaction in transactions.data"
                :key="transaction.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700"
            >
                <div class="flex items-center gap-4">
                    <div
                        :class="[
                            'rounded-full p-3',
                            transaction.type === 'sent'
                                ? 'bg-red-100 dark:bg-red-950'
                                : 'bg-green-100 dark:bg-green-950',
                        ]"
                    >
                        <svg
                            v-if="transaction.type === 'sent'"
                            class="h-5 w-5 text-red-600 dark:text-red-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"
                            />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{
                                transaction.type === 'sent'
                                    ? `To ${transaction.receiver.name}`
                                    : `From ${transaction.sender.name}`
                            }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(transaction.created_at) }}
                        </p>
                        <p
                            v-if="transaction.type === 'sent'"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        >
                            Fee: ${{ transaction.commission_fee }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p
                        :class="[
                            'text-lg font-semibold',
                            transaction.type === 'sent'
                                ? 'text-red-600 dark:text-red-400'
                                : 'text-green-600 dark:text-green-400',
                        ]"
                    >
                        {{ transaction.type === 'sent' ? '-' : '+' }}${{
                            transaction.amount
                        }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ transaction.status }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination
            :meta="transactions.meta"
            :links="transactions.links"
            :route-function="index"
        />
    </div>
</template>
