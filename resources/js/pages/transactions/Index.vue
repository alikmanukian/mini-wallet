<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/transactions';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Transaction {
    id: number;
    type: 'sent' | 'received';
    amount: string;
    commission_fee: string;
    status: string;
    sender: User;
    receiver: User;
    created_at: string;
}

interface TransactionErrors {
    receiver_id?: string;
    amount?: string;
    transaction?: string;
}

interface FlashMessage {
    success?: string;
    error?: string;
}

interface PaginatedData<T> {
    data: T[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        per_page: number;
        to: number;
        total: number;
    };
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
}

interface Props {
    balance: string;
    transactions: PaginatedData<Transaction>;
    users: User[];
    flash?: FlashMessage;
    errors?: TransactionErrors;
}

defineProps<Props>();

const form = useForm({
    receiver_id: '',
    amount: '',
});

const calculatedCommission = computed(() => {
    const amount = parseFloat(form.amount);
    if (isNaN(amount) || amount <= 0) return '0.00';
    return (amount * 0.015).toFixed(2);
});

const totalDeduction = computed(() => {
    const amount = parseFloat(form.amount);
    const commission = parseFloat(calculatedCommission.value);
    if (isNaN(amount) || amount <= 0) return '0.00';
    return (amount + commission).toFixed(2);
});

const submit = () => {
    form.post('/transactions', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: index(),
    },
];
</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Success Message -->
            <div
                v-if="flash?.success"
                class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200"
            >
                {{ flash.success }}
            </div>

            <!-- Error Message -->
            <div
                v-if="errors?.transaction"
                class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200"
            >
                {{ errors.transaction }}
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
            <div
                class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
            >
                <h2
                    class="mb-4 text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Send Money
                </h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="receiver_id">Recipient</Label>
                        <Select
                            v-model="form.receiver_id"
                            :disabled="form.processing"
                        >
                            <SelectTrigger id="receiver_id" class="w-full">
                                <SelectValue placeholder="Select a recipient" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="String(user.id)"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.receiver_id" />
                    </div>

                    <div class="space-y-2">
                        <Label for="amount">Amount</Label>
                        <div class="relative">
                            <span
                                class="absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground"
                            >
                                $
                            </span>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="pl-8"
                                placeholder="0.00"
                                :disabled="form.processing"
                            />
                        </div>
                        <InputError :message="form.errors.amount" />
                    </div>

                    <!-- Transaction Summary -->
                    <div
                        v-if="form.amount && parseFloat(form.amount) > 0"
                        class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800"
                    >
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Amount to send:</span
                                >
                                <span class="font-medium dark:text-white"
                                    >${{
                                        parseFloat(form.amount).toFixed(2)
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Commission (1.5%):</span
                                >
                                <span class="font-medium dark:text-white"
                                    >${{ calculatedCommission }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700"
                            >
                                <span
                                    class="font-semibold text-gray-900 dark:text-white"
                                    >Total deduction:</span
                                >
                                <span
                                    class="font-semibold text-gray-900 dark:text-white"
                                    >${{ totalDeduction }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <Button
                        type="submit"
                        :disabled="
                            form.processing || !form.receiver_id || !form.amount
                        "
                        class="w-full"
                    >
                        {{ form.processing ? 'Processing...' : 'Send Money' }}
                    </Button>
                </form>
            </div>

            <!-- Transaction History -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
            >
                <h2
                    class="mb-4 text-xl font-semibold text-gray-900 dark:text-white"
                >
                    Transaction History
                </h2>

                <div
                    v-if="transactions.data.length === 0"
                    class="py-12 text-center"
                >
                    <p class="text-gray-500 dark:text-gray-400">
                        No transactions yet
                    </p>
                </div>

                <div v-else class="space-y-3">
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
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{
                                        transaction.type === 'sent'
                                            ? `To ${transaction.receiver.name}`
                                            : `From ${transaction.sender.name}`
                                    }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
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
        </div>
    </AppLayout>
</template>
