import { useState, useEffect } from 'react';
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

    useEffect(() => {
        const fetchProducts = async () => {
            try {
                setCardsLoading(true);
                setError(null);
                const { data } = await axios.get('/products');

                if (data.success) {
                    setProducts(data.data || []);
                    setTotalPages(1);
                } else {
                    setError(data.error);
                }
            } catch (err) {
                setError('Error loading products');
            } finally {
                setCardsLoading(false);
            }
        };

        fetchProducts();
    }, []);

    const goToAddProduct = () => {
        navigate('/product/new');
    };

    const goToProduct = () => {
        navigate(`/product/${product.id}`);
    };

    const handlePageChange = (newPage) => setPage(newPage);

    const renderCards = () => {
        if (cardsLoading) {
            // Show loading skeleton cards
            return (
                <>
                    <AddProductCard onClick={goToAddProduct} />
                    {Array.from({ length: 7 }, (_, i) => (
                        <LoadingCard key={`loading-${i}`} />
                    ))}
                </>
            );
        }

        // Show real products and placeholders
        const placeholderCards = [];
        const totalCards = 8;
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
        <div className="flex flex-col h-full w-full px-4 pt-6 pb-6 justify-center">
            <h1 className="text-2xl font-bold text-primaryText mb-6">Explore Items</h1>
            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {error}
                </div>
            )}
            {/* Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 place-items-start">
                {renderCards()}
            </div>
            {/* Pagination */}
            <Pagination
                page={page}
                totalPages={totalPages}
                onPageChange={handlePageChange}
            />
        </div>
    );
}