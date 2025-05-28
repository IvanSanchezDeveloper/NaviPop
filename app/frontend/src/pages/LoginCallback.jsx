import { useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

export default function LoginCallback() {
    const [params] = useSearchParams();
    const navigate = useNavigate();

    useEffect(() => {
        const error = params.get('error');

        if (error) {
            navigate('/login', { state: { error: error } });
            return
        }

        const token = params.get('token');
        if (token) {
            localStorage.setItem('jwt', token);
            navigate('/dashboard');
        }
    }, [params]);

    return <p>Redirecting...</p>;
}
