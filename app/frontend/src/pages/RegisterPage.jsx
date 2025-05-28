import { useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { FcGoogle } from 'react-icons/fc';

export default function RegisterPage() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const backendUrl = import.meta.env.VITE_BACKEND_URL;

    const handleRegister = async (e) => {
        e.preventDefault();
        try {
            const { data } = await axios.post(`${backendUrl}/api/register`, { email, password, name});
            localStorage.setItem('jwt', data.token);
            window.location.href = '/dashboard';
        } catch (err) {
            const message = err?.response?.data?.message || 'Registration failed';
            setError(message);
        }
    };

    const handleGoogleRegister = () => {
        window.location.href = `${backendUrl}/api/login/google`;
    };

    return (
        <div className="flex flex-col items-center justify-center px-4">
            <div className="w-full max-w-md p-8 rounded-xl shadow-md bg-primary overflow-y-auto">
                <h2 className="text-3xl font-bold text-center text-primaryText mb-8">
                    Create your account
                </h2>

                <form onSubmit={handleRegister} className="space-y-6">
                    <input
                        type="text"
                        placeholder="Full Name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />
                    <input
                        type="email"
                        placeholder="E-mail"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />

                    <input
                        type="password"
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />

                    {error && (
                        <div className="text-sm font-medium text-secondaryText">{error}</div>
                    )}

                    <button
                        type="submit"
                        className="w-full py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition"
                    >
                        Register
                    </button>
                </form>

                <div className="flex items-center my-8">
                    <hr className="flex-grow border-gray-300"/>
                    <span className="mx-4 text-sm text-primaryText select-none">or</span>
                    <hr className="flex-grow border-gray-300"/>
                </div>

                <button
                    onClick={handleGoogleRegister}
                    className="w-full flex items-center justify-center gap-3 py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition"
                >
                    <FcGoogle className="text-2xl"/>
                    Register with Google
                </button>

                <div className="mt-6 text-center">
                    <p className="text-sm text-primaryText">
                        Already have an account?{' '}
                        <Link to="/login" className="text-secondaryText hover:underline font-medium">
                            Sign in here
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    );
}
