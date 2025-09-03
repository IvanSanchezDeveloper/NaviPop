import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ProductCard, PlaceholderCard, AddProductCard, LoadingCard } from '../components/ProductCard';
import Pagination from '../components/Pagination';
import { useProducts } from '../hooks/useProducts';

export default function HomePage() {
    const [page, setPage] = useState(1);
    const ITEMS_PER_PAGE = 8;
    const navigate = useNavigate();

    const { products, totalPages, loading: cardsLoading, error } = useProducts(page, ITEMS_PER_PAGE);

    const goToAddProduct = () => navigate('/product/new');

    const handlePageChange = (newPage) => setPage(newPage);

    const renderCards = () => {
        if (cardsLoading) {
            return (
                <>
                    <AddProductCard onClick={goToAddProduct} />
                    {Array.from({ length: ITEMS_PER_PAGE - 1 }).map((_, i) => (
                        <LoadingCard key={i} />
                    ))}
                </>
            );
        }

        const placeholdersCount = Math.max(0, ITEMS_PER_PAGE - 1 - products.length);
        return (
            <>
                <AddProductCard onClick={goToAddProduct} />
                {products.map((product) => <ProductCard key={product.id} product={product} />)}
                {Array.from({ length: placeholdersCount }).map((_, i) => (
                    <PlaceholderCard key={i} type="empty" />
                ))}
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

            <Pagination page={page} totalPages={totalPages} onPageChange={handlePageChange} />
        </div>
    );
}
