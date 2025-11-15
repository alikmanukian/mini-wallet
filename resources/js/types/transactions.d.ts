export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Transaction {
    id: number;
    type: 'sent' | 'received';
    amount: string;
    commission_fee: string;
    status: string;
    sender: User;
    receiver: User;
    created_at: string;
}
