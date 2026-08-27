import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    scenarios: {
        auth_flood: {
            executor: 'constant-arrival-rate',
            rate: 200,
            timeUnit: '1s',
            duration: '30s',
            preAllocatedVUs: 200,
            maxVUs: 1000,
        },
    },
    thresholds: {
        http_req_duration: ['p(95)<350'], // Costo de hashing < 350ms
        http_req_failed: ['rate<0.05'],
    },
};

const BASE_URL = __ENV.TARGET_URL || 'http://127.0.0.1:8000';

export default function () {
    const payload = JSON.stringify({
        phone: '+5917' + Math.floor(1000000 + Math.random() * 9000000),
        password: 'IncorrectPasswordAttempt!',
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Forwarded-For': '190.181.45.' + Math.floor(1 + Math.random() * 254),
        },
    };

    const res = http.post(`${BASE_URL}/login`, payload, params);

    check(res, {
        'rate limit or validation caught': (r) => r.status === 422 || r.status === 429 || r.status === 302,
    });

    sleep(0.05);
}