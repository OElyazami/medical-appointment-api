const BASE = '/api';

export class ApiError extends Error {
    constructor(
        message: string,
        public status: number,
        public errors?: Record<string, string[]>,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

export async function request<T>(url: string, options?: RequestInit): Promise<T> {
    const res = await fetch(`${BASE}${url}`, {
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        ...options,
    });

    const data = await res.json();

    if (!res.ok) {
        const message = data.message || `Request failed with status ${res.status}`;
        throw new ApiError(message, res.status, data.errors);
    }

    return data;
}
