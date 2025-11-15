<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { User } from '@/types/transactions';

interface Props {
    users: User[];
    commissionRate: number;
}

interface Emits {
    (
        e: 'transactionCreated',
        data: {
            receiver: User;
            amount: number;
            commissionFee: number;
            totalDeducted: number;
        },
    ): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const form = useForm({
    receiver_id: '',
    amount: '',
});

const commissionPercentage = computed(() =>
    (props.commissionRate * 100).toFixed(1),
);

const calculatedCommission = computed(() => {
    const amount = parseFloat(form.amount);
    return isNaN(amount) || amount <= 0
        ? '0.00'
        : (amount * props.commissionRate).toFixed(2);
});

const totalDeduction = computed(() => {
    const amount = parseFloat(form.amount);
    const commission = parseFloat(calculatedCommission.value);
    return isNaN(amount) || amount <= 0
        ? '0.00'
        : (amount + commission).toFixed(2);
});

const submit = () => {
    const receiverId = parseInt(form.receiver_id);
    const amount = parseFloat(form.amount);
    const commissionFee = parseFloat(calculatedCommission.value);
    const totalDeducted = parseFloat(totalDeduction.value);

    const receiver = props.users.find((u) => u.id === receiverId);

    form.post('/transactions', {
        preserveScroll: true,
        onSuccess: () => {
            if (receiver) {
                emit('transactionCreated', {
                    receiver,
                    amount,
                    commissionFee,
                    totalDeducted,
                });

                toast.success(`Successfully sent $${amount.toFixed(2)} to ${receiver.name}`);
            }

            form.reset();
        },
    });
};
</script>

<template>
    <div
        class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-sidebar"
    >
        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">
            Send Money
        </h2>
        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <Label for="receiver_id">Recipient</Label>
                <Select v-model="form.receiver_id" :disabled="form.processing">
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
                    <span class="absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground">$</span>
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
                            >${{ parseFloat(form.amount).toFixed(2) }}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400"
                            >Commission ({{ commissionPercentage }}%):</span
                        >
                        <span class="font-medium dark:text-white"
                            >${{ calculatedCommission }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700"
                    >
                        <span class="font-semibold text-gray-900 dark:text-white"
                            >Total deduction:</span
                        >
                        <span class="font-semibold text-gray-900 dark:text-white"
                            >${{ totalDeduction }}</span
                        >
                    </div>
                </div>
            </div>

            <Button
                type="submit"
                :disabled="form.processing || !form.receiver_id || !form.amount"
                class="w-full"
            >
                {{ form.processing ? 'Processing...' : 'Send Money' }}
            </Button>
        </form>
    </div>
</template>
