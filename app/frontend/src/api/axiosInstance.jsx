import axios from 'axios';

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const instance = axios.create({
    baseURL: '${backendUrl}/api',
});

instance.interceptors.request.use((config) => {
    const token = localStorage.getItem('jwt');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default instance;
