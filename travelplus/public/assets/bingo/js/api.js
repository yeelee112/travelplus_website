export async function apiGet(path, params = {}) {
    const url = new URL(normalizePath(path), baseUrl());
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, value);
        }
    });

    const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    return parseResponse(response);
}

export async function apiPost(path, body = {}) {
    const response = await fetch(new URL(normalizePath(path), baseUrl()).toString(), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });

    return parseResponse(response);
}

function baseUrl() {
    return document.querySelector('meta[name="bingo-base-url"]')?.content || window.location.origin;
}

function normalizePath(path) {
    return String(path).replace(/^\/+/, '');
}

async function parseResponse(response) {
    const payload = await response.json();
    if (!response.ok || payload.success === false) {
        throw new Error(payload.message || 'Request failed');
    }

    return payload.data;
}
