export interface User {
    id: number;
    name: string;
    email: string;
    username: string;
    email_verified_at: string;
    roles: any;
    created_at: string;
    photo: string;
    address: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};
