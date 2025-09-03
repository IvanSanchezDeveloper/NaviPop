import { useState, useEffect, useRef, useCallback } from 'react';
import axios from '../api/axiosInstance.jsx';

const preloadImage = (product) =>
    new Promise((resolve) => {
        if (!product.image) return resolve();

        const img = new Image();
        img.src = product.image;

        img.onload = () => resolve();
        img.onerror = () => {
            const fallback = new Image();
            fallback.src = "/logo192.png";
            fallback.onload = () => {
                product.image = null;
                resolve();
            };
            fallback.onerror = resolve;
        };
    });

const preloadImages = async (products) => {
    await Promise.all(products.map(preloadImage));
};

export const useProducts = (page, itemsPerPage) => {
    const [products, setProducts] = useState([]);
    const [totalPages, setTotalPages] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const pageCache = useRef({});

    const fetchPage = useCallback(
        async (pageNum, signal) => {
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
        },
        [itemsPerPage]
    );

    const prefetchNextPage = useCallback(
        async (nextPage) => {
            if (nextPage > totalPages || pageCache.current[nextPage]) return;
            try {
                await fetchPage(nextPage);
            } catch (err) {
                console.warn(`Failed to prefetch page ${nextPage}:`, err.message);
            }
        },
        [fetchPage, totalPages]
    );

    useEffect(() => {
        const controller = new AbortController();
        const fetchProducts = async () => {
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
        fetchProducts();
        return () => controller.abort();
    }, [page, fetchPage, prefetchNextPage]);

    return { products, totalPages, loading, error };
};

export const useProduct = (id) => {
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!id) return;
        const controller = new AbortController();

        const fetchProduct = async () => {
            setLoading(true);
            setError(null);
            try {
                const { data } = await axios.get(`/products/${id}`, { signal: controller.signal });
                if (data.success && data.data) {
                    const productData = data.data;
                    await preloadImage(productData);
                    setProduct(productData);
                }
                else {
                    setError(data.error || 'Failed to load product');
                    setProduct(null);
                }
            } catch (err) {
                if (!controller.signal.aborted) setError('Error loading product');
                setProduct(null);
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        };

        fetchProduct();
        return () => controller.abort();

    }, [id]);

    return { product, loading, error };
};
