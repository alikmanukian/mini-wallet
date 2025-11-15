<script setup lang="ts">
import TransactionForm from '@/components/TransactionForm.vue';
import TransactionsList from '@/components/TransactionsList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/transactions';
import type { AppPageProps, BreadcrumbItem, PaginatedData } from '@/types';
import type { Transaction, User } from '@/types/transactions';
import { Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

type PageProps = AppPageProps<{
    balance: string;
    transactions: PaginatedData<Transaction>;
    users: User[];
    commission_rate: number;
}>;

const props = defineProps<PageProps>();

const balance = ref(props.balance);
const transactions = ref(props.transactions);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Transactions', href: index().url },
];

const addTransaction = (transaction: Transaction, balanceChange: number) => {
    balance.value = (parseFloat(balance.value) + balanceChange).toFixed(2);
    transactions.value.data.unshift(transaction);
    transactions.value.meta.total += 1;
};

const handleTransactionCreated = (data: {
    receiver: User;
    amount: number;
    commissionFee: number;
    totalDeducted: number;
}) => {
    const currentUser = props.auth?.user;

    if (!currentUser) {
        return;
    }

    addTransaction(
        {
            id: Date.now(),
            type: 'sent',
            amount: data.amount.toFixed(2),
            commission_fee: data.commissionFee.toFixed(2),
            status: 'completed',
            sender: currentUser,
            receiver: data.receiver,
            created_at: new Date().toISOString(),
        },
        -data.totalDeducted,
    );
};

onMounted(() => {
    const currentUser = props.auth?.user;

    if (!currentUser || !window.Echo) return;

    window.Echo.private(`user.${currentUser.id}`).listen(
        '.transaction.processed',
        (event: any) => {
            const { transaction: newTransaction } = event;

            // Only update UI if current user is the RECEIVER
            if (newTransaction.receiver_id !== currentUser.id) {
                return;
            }

            addTransaction(
                {
                    id: newTransaction.id,
                    type: 'received',
                    amount: newTransaction.amount,
                    commission_fee: newTransaction.commission_fee,
                    status: newTransaction.status,
                    sender: newTransaction.sender,
                    receiver: newTransaction.receiver,
                    created_at: newTransaction.created_at,
                },
                parseFloat(newTransaction.amount),
            );
        },
    );
});

onUnmounted(() => {
    const currentUser = props.auth?.user;
    if (currentUser && window.Echo) {
        window.Echo.leave(`user.${currentUser.id}`);
    }
});
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Success Message -->
            <div
                v-if="props.flash?.success"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200"
            >
                {{ props.flash.success }}
            </div>

            <!-- Error Message -->
            <div
                v-if="props.errors?.transaction"
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
            >
                {{ props.errors.transaction }}
            </div>

            <!-- Balance Card -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p
                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                        >
                            Current Balance
                        </p>
                        <p
                            class="mt-2 text-4xl font-bold text-gray-900 dark:text-white"
                        >
                            ${{ balance }}
                        </p>
                    </div>
                    <div
                        class="rounded-full bg-green-100 p-4 dark:bg-green-950"
                    >
                        <svg
                            class="h-8 w-8 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Transfer Form -->
            <TransactionForm
                :users="props.users"
                :commission-rate="props.commission_rate"
                @transaction-created="handleTransactionCreated"
            />

            <!-- Transaction History -->
            <TransactionsList :transactions="transactions" />
        </div>
    </AppLayout>
</template>
