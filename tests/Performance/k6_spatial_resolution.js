import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    scenarios: {
        spatial_evaluation: {
            executor: 'ramping-arrival-rate',
            startRate: 50,
            timeUnit: '1s',
            preAllocatedVUs: 100,
            maxVUs: 500,
            stages: [
                { duration: '30s', target: 200 },
                { duration: '1m', target: 500 },
                { duration: '30s', target: 0 },
            ],
        },
    },
    thresholds: {
        http_req_duration: ['p(95)<150'], // 95% de peticiones bajo 150ms
        http_req_failed: ['rate<0.01'],    // Errores < 1%
    },
};

const BASE_URL = __ENV.TARGET_URL || 'http://127.0.0.1:8000';

export default function () {
    // Rango de prueba centrado en coordenadas del Valle de La Paz
    const lat = -16.55 + (Math.random() * 0.15);
    const lng = -68.20 + (Math.random() * 0.15);

    const payload = JSON.stringify({
        latitude: lat,
        longitude: lng,
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    const res = http.post(`${BASE_URL}/register/validate-step-1`, payload, params);

    check(res, {
        'status is 200 or 302': (r) => r.status === 200 || r.status === 302,
        'response time OK': (r) => r.timings.duration < 150,
    });

    sleep(0.1);
}