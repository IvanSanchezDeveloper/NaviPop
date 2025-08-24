import { createContext, useContext, useState, useEffect } from 'react';
import axios from '../api/axiosInstance.jsx';
import { useLoading } from './LoadingContext';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const { setIsLoading } = useLoading();
    const [isInitialized, setIsInitialized] = useState(false);

    const checkAuth = async () => {
        try {
            setIsLoading(true);
            const response = await axios.get('/user/me')
            setUser(response.data);
            return response.data;
        } catch (error) {
            setUser(null);
            return null;
        } finally {
            setIsLoading(false);
        }
    };

    const setGoogleLoginCookie = async (oneTimeCode) => {
        try {
            const response = await axios.post('/login/google/cookie',
                { oneTimeCode }
            );
            await checkAuth();
            return { success: true };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.error || 'Error while loging in'
            };
        }
    }

    const login = async (email, password) => {
        try {
            const response = await axios.post('/login',
                { email, password }
            );
            await checkAuth();
            return { success: true };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.error || 'Error while loging in'
            };
        }
    };

    const register = async (email, password, name) => {
        try {
            const response = await axios.post(`/register`, { email, password, name});
            await checkAuth();
            return { success: true };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.error || 'Error while registering'
            };
        }
    };

    const logout = async () => {
        try {
            setIsLoading(true);
            await axios.post('/logout', {});
        } finally {
            setUser(null);
            setIsLoading(false);
        }

    };

    useEffect(() => {
        checkAuth();
    }, []);

    const value = {
        user,
        login,
        logout,
        register,
        setGoogleLoginCookie,
        checkAuth
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used inside an AuthProvider');
    }
    return context;
};