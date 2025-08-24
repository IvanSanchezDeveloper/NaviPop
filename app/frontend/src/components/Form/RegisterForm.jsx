import { useState } from 'react';
import { Link } from 'react-router-dom';
import { FcGoogle } from 'react-icons/fc';
import { useAuth } from '../../contexts/AuthContext';
import { useGoogleAuth } from "../../hooks/useGoogleAuth";

export default function RegisterForm() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const backendUrl = import.meta.env.VITE_BACKEND_URL;
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { register } = useAuth();
    const { startGoogleAuth } = useGoogleAuth(backendUrl, setError);

    const handleRegister = async (e) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            const result = await register(email, password);

            if (!result.success) {
                setError(result.error);
            }

        } finally {
            setIsSubmitting(false);
        }
    };
    const handleGoogleRegister = () => {
        startGoogleAuth();
    };

    return (
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
                    <div className="text-sm font-medium text-error">{error}</div>
                )}

                <button
                    type="submit"
                    className="w-full py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition"
                >
                    {isSubmitting ? (
                        <span className="flex items-center justify-center">
                            <div className="animate-spin h-5 w-5 border-2 border-gray-500 rounded-full border-t-transparent" />
                        </span>
                    ) : (
                        'Register'
                    )}
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
    );
}