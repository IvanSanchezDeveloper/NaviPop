import axios from 'axios';

const backendUrl = import.meta.env.VITE_BACKEND_URL;

const axiosInstance = axios.create({
    baseURL: `${backendUrl}/api`,
    withCredentials: true
});

axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default axiosInstance;
