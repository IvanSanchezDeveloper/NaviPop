import { useState } from 'react';
import axios from 'axios';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');

    const backendUrl = import.meta.env.VITE_BACKEND_URL;

    const handleLogin = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post(`${backendUrl}/api/login`, {
                email,
                password,
            });

            const { token } = response.data;
            localStorage.setItem('jwt', token);
            // Redirige o navega al home
            window.location.href = '/dashboard'; // o usa React Router
        } catch (err) {
            setError('Email o contraseña incorrectos');
        }
    };

    const handleGoogleLogin = () => {
        window.location.href = `${backendUrl}/api/login/google`;
    };

    return (
        <div style={{ maxWidth: 400, margin: '50px auto' }}>
            <h2>Login</h2>
            <form onSubmit={handleLogin}>
                <div>
                    <input
                        type="email"
                        placeholder="Correo electrónico"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        style={{ width: '100%', marginBottom: 10 }}
                    />
                </div>
                <div>
                    <input
                        type="password"
                        placeholder="Contraseña"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        style={{ width: '100%', marginBottom: 10 }}
                    />
                </div>
                {error && <div style={{ color: 'red' }}>{error}</div>}
                <button type="submit" style={{ width: '100%' }}>Iniciar sesión</button>
            </form>

            <hr />

            <button onClick={handleGoogleLogin} style={{ width: '100%', marginTop: 10 }}>
                Iniciar sesión con Google
            </button>
        </div>
    );
}
