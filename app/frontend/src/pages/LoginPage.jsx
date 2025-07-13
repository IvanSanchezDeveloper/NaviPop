import { useState, useEffect } from 'react';
import {Link, useSearchParams, useNavigate, useLocation, Navigate} from 'react-router-dom';
import axios from 'axios';
import { FcGoogle } from 'react-icons/fc';
import { useAuth } from '../contexts/AuthContext';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const navigate = useNavigate();
    const { login, logout, user, loading } = useAuth();
    const backendUrl = import.meta.env.VITE_BACKEND_URL;
    const [searchParams] = useSearchParams();

    useEffect(() => {
        const errorParam = searchParams.get('error');
        if (errorParam) {
            console.log('🔴 Error capturado:', errorParam);
            setError(decodeURIComponent(errorParam));

            const url = new URL(window.location);
            url.searchParams.delete('error');
            window.history.replaceState({}, '', url);
        }
    }, []);

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            const result = await login(email, password);

            if (!result.success) {
                setError(result.error);
            }

        } finally {
            setIsSubmitting(false);
        }
    };


    const handleGoogleLogin = () => {
        window.location.href = `${backendUrl}/api/login/google`;
    };

    return (
        <div className="flex flex-col items-center justify-center px-4">
            <div className="w-full max-w-md p-8 rounded-xl shadow-md bg-primary overflow-y-auto">
                <h2 className="text-3xl font-bold text-center text-primaryText mb-8">
                    Sign in to NAVIPop
                </h2>

                <form onSubmit={handleLogin} className="space-y-6">
                    <input
                        type="email"
                        placeholder="E-mail"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        disabled={isSubmitting}
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />

                    <input
                        type="password"
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        required
                        disabled={isSubmitting}
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />

                    {error && (
                        <div className="text-sm font-medium text-secondaryText">{error}</div>
                    )}

                    <button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition"
                    >
                        {isSubmitting ? (
                            <span className="flex items-center justify-center">
                                <div className="animate-spin h-5 w-5 border-2 border-gray-500 rounded-full border-t-transparent" />
                            </span>
                        ) : (
                            'Sign in'
                        )}

                    </button>
                </form>

                <div className="w-full text-right mt-2">
                    <Link to="/forgot-password" className="text-sm text-secondaryText hover:underline">
                        Forgot your password?
                    </Link>
                </div>

                <div className="flex items-center my-8">
                    <hr className="flex-grow border-gray-300"/>
                    <span className="mx-4 text-sm text-primaryText select-none">or</span>
                    <hr className="flex-grow border-gray-300"/>
                </div>

                <button
                    onClick={handleGoogleLogin}
                    disabled={isSubmitting}
                    className="w-full flex items-center justify-center gap-3 py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition"
                >
                    <FcGoogle className="text-2xl"/>
                    Sign in with Google
                </button>

                <div className="mt-6 text-center">
                    <p className="text-sm text-primaryText">
                        Don’t have an account yet?{' '}
                        <Link to="/register" className="text-secondaryText hover:underline font-medium">
                            Register here
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    );
}
