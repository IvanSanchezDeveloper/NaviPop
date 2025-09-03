import { useState, useEffect, useRef, useCallback } from 'react';
import axios from '../api/axiosInstance.jsx';

const preloadImage = (src) => {
    return new Promise((resolve, reject) => {
        if (!src) return resolve();
        const img = new Image();
        img.src = src;
        img.onload = resolve;
        img.onerror = reject;
    });
};

const preloadImages = async (products) => {
    await Promise.all(products.map(p => preloadImage(p.image)));
};

export const useProducts = (page, itemsPerPage) => {
    const [products, setProducts] = useState([]);
    const [totalPages, setTotalPages] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const pageCache = useRef({});

    const fetchPage = useCallback(async (pageNum, signal) => {
        if (pageCache.current[pageNum]) return pageCache.current[pageNum];

        try {
            const { data } = await axios.get('/products', {
                params: { page: pageNum, limit: itemsPerPage - 1 },
                signal,
            });

            if (!data.success) throw new Error(data.error || 'Failed to fetch products');

            const products = data.data || [];
            const totalPages = data.pagination?.total_pages || 1;

            await preloadImages(products);

            const pageData = { products, totalPages };
            pageCache.current[pageNum] = pageData;

            return pageData;
        } catch (err) {
            if (signal?.aborted) return null;
            throw err;
        }
    }, [itemsPerPage]);

    const prefetchNextPage = useCallback(async (nextPage) => {
        if (nextPage > totalPages || pageCache.current[nextPage]) return;
        try {
            await fetchPage(nextPage);
        } catch (err) {
            console.warn(`Failed to prefetch page ${nextPage}:`, err.message);
        }
    }, [fetchPage, totalPages]);


    useEffect(() => {
        const controller = new AbortController();

        const load = async () => {
            setLoading(true);
            setError(null);

            try {
                const pageData = await fetchPage(page, controller.signal);
                if (!pageData) return;

                setProducts(pageData.products);
                setTotalPages(pageData.totalPages);

                prefetchNextPage(page + 1);
            } catch (err) {
                if (!controller.signal.aborted) setError(err.message || 'Error loading products');
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        };

        load();

        return () => controller.abort();

    }, [page, fetchPage, prefetchNextPage]);

    return { products, totalPages, loading, error };
};
