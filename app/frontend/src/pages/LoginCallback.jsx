import { useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';

export default function LoginCallback() {
    const [params] = useSearchParams();

    useEffect(() => {
        const token = params.get('token');
        if (token) {
            localStorage.setItem('jwt', token);
            window.location.href = '/dashboard';
        }
    }, [params]);

    return <p>Redirecting...</p>;
}
