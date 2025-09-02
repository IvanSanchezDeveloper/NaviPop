import { useState, useEffect, useRef, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../api/axiosInstance.jsx';
import { ProductCard, PlaceholderCard, AddProductCard, LoadingCard } from '../components/ProductCard';
import Pagination from '../components/Pagination';

export default function HomePage() {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [cardsLoading, setCardsLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const ITEMS_PER_PAGE = 8;

    const pageCache = useRef({});

    const prefetchNextPage = useCallback(async (nextPage, totalPages) => {
        if (nextPage > totalPages || pageCache.current[nextPage]) {
            return;
        }

        try {
            const { data } = await axios.get('/products', {
                params: { page: nextPage, limit: ITEMS_PER_PAGE - 1 },
            });

            if (data.success) {
                const newProducts = data.data || [];
                const newTotalPages = data.pagination?.total_pages || 1;
                pageCache.current[nextPage] = { products: newProducts, totalPages: newTotalPages };
            }
        } catch (err) {
            console.warn(`Failed to prefetch page ${nextPage}:`, err.message);
        }
    }, [ITEMS_PER_PAGE]);

    useEffect(() => {
        const controller = new AbortController();
        const fetchProducts = async () => {
            if (pageCache.current[page]) {
                const { products: cachedProducts, totalPages: cachedTotalPages } = pageCache.current[page];
                setProducts(cachedProducts);
                setTotalPages(cachedTotalPages);
                setCardsLoading(false);
                prefetchNextPage(page + 1, cachedTotalPages);
                return;
            }

            try {

                setCardsLoading(true);
                setError(null);

                const { data } = await axios.get('/products', {
                    params: {
                        page,
                        limit: ITEMS_PER_PAGE - 1,
                    },
                    signal: controller.signal,
                });

                if (data.success) {
                    const newProducts = data.data || [];
                    const newTotalPages = data.pagination?.total_pages || 1;

                    setProducts(newProducts);
                    setTotalPages(newTotalPages);

                    pageCache.current[page] = { products: newProducts, totalPages: newTotalPages };

                    prefetchNextPage(page + 1, newTotalPages);
                } else {
                    setError(data.error);
                }
            } catch (err) {
                if (controller.signal.aborted) return;
                setError('Error loading products');
            } finally {
                if (!controller.signal.aborted) setCardsLoading(false);
            }
        };

        fetchProducts();

        return () => {
            controller.abort();
        };
    }, [page, prefetchNextPage]);

    const goToAddProduct = () => {
        navigate('/product/new');
    };

    const handlePageChange = (newPage) => {
        setPage(newPage);
    };

    const renderCards = () => {
        if (cardsLoading) {
            return (
                <>
                    <AddProductCard onClick={goToAddProduct} />
                    {Array.from({ length: ITEMS_PER_PAGE - 1 }, (_, i) => (
                        <LoadingCard key={`loading-${i}`} />
                    ))}
                </>
            );
        }

        const placeholderCards = [];
        const totalCards = ITEMS_PER_PAGE;
        const usedSlots = products.length + 1; // +1 for add product card

        // Content placeholders (3 cards with content)
        for (let i = 0; i < Math.min(3, totalCards - usedSlots); i++) {
            placeholderCards.push(
                <PlaceholderCard key={`placeholder-content-${i}`} type="content" />
            );
        }

        // Empty placeholders (remaining slots)
        const remainingSlots = Math.max(0, totalCards - usedSlots - 3);
        for (let i = 0; i < remainingSlots; i++) {
            placeholderCards.push(
                <PlaceholderCard key={`placeholder-empty-${i}`} type="empty" />
            );
        }

        return (
            <>
                <AddProductCard onClick={goToAddProduct} />
                {products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                ))}
                {placeholderCards}
            </>
        );
    };

    return (
        <div className="flex flex-col h-full w-full max-w-7xl mx-auto px-4 pt-6 pb-6 justify-center">
            <h1 className="text-2xl font-bold text-primaryText mb-6">Explore Items</h1>
            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {error}
                </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 place-items-start">
                {renderCards()}
            </div>

            <Pagination
                page={page}
                totalPages={totalPages}
                onPageChange={handlePageChange}
            />
        </div>
    );
}
