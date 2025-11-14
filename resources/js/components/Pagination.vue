<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { router } from '@inertiajs/vue3';

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

interface Props {
    meta: PaginationMeta;
    links: PaginationLinks;
    routeFunction: (params?: { query?: { page?: number } }) => string;
}

const props = defineProps<Props>();

const goToPage = (page: number) => {
    router.visit(props.routeFunction({ query: { page } }));
};

const goToPreviousPage = () => {
    const page = props.meta.current_page > 1 ? props.meta.current_page - 1 : 1;
    goToPage(page);
};

const goToNextPage = () => {
    const page =
        props.meta.current_page < props.meta.last_page
            ? props.meta.current_page + 1
            : props.meta.last_page;
    goToPage(page);
};
</script>

<template>
    <Pagination
        v-if="meta.last_page > 1"
        v-slot="{ page }"
        :total="meta.total"
        :sibling-count="1"
        :default-page="meta.current_page"
        :items-per-page="meta.per_page"
        class="mt-6"
    >
        <PaginationContent v-slot="{ items }" class="gap-1">
            <PaginationItem
                :value="meta.current_page > 1 ? meta.current_page - 1 : 1"
            >
                <PaginationPrevious @click="goToPreviousPage">
                    <ChevronLeft class="h-4 w-4" />
                </PaginationPrevious>
            </PaginationItem>

            <template v-for="(item, idx) in items" :key="idx">
                <PaginationItem
                    v-if="item.type === 'page'"
                    :value="item.value"
                    :is-active="item.value === page"
                    @click="goToPage(item.value)"
                >
                    {{ item.value }}
                </PaginationItem>
                <PaginationEllipsis
                    v-else
                    :key="item.type"
                    :index="idx"
                />
            </template>

            <PaginationItem
                :value="
                    meta.current_page < meta.last_page
                        ? meta.current_page + 1
                        : meta.last_page
                "
            >
                <PaginationNext @click="goToNextPage">
                    <ChevronRight class="h-4 w-4" />
                </PaginationNext>
            </PaginationItem>
        </PaginationContent>
    </Pagination>
</template>
