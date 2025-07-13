import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { ProductCard, PlaceholderCard, AddProductCard } from '../components/ProductCard';
import Pagination from '../components/Pagination';

export default function HomePage() {
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const backendUrl = import.meta.env.VITE_BACKEND_URL;
    const navigate = useNavigate();

    // TODO: Load products with pagination
    // useEffect(() => {
    //     const fetchProducts = async () => {
    //         try {
    //             const { data } = await axios.get(`${backendUrl}/api/products?page=${page}&limit=7`);
    //             setProducts(data.items || []);
    //             setTotalPages(data.totalPages || 1);
    //         } catch (err) {
    //             console.error('Error fetching products:', err);
    //         }
    //     };
    //     fetchProducts();
    // }, [page]);

    const goToAddProduct = () => {

    };

    const handlePageChange = (newPage) => setPage(newPage);

    // Generate placeholder cards, temporary until I get real fixtures
    const generatePlaceholderCards = () => {
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

        return placeholderCards;
    };

    return (
        <div className="relative flex flex-col min-h-full w-full px-4 pt-6 pb-6">
            <h1 className="text-2xl font-bold text-primaryText mb-6">Explore Items</h1>

            {/* Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 place-items-start">
                {/* Add Product Card (always first) */}
                <AddProductCard onClick={goToAddProduct} />

                {/*/!* Real Product Cards *!/*/}
                {/*{products.map((product) => (*/}
                {/*    <ProductCard key={product.id} product={product} />*/}
                {/*))}*/}

                {/* Placeholder Cards */}
                {generatePlaceholderCards()}
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