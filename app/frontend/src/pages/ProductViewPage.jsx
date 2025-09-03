import { useParams } from "react-router-dom";
import { useEffect } from 'react';
import { useLoading } from '../contexts/LoadingContext';
import { useProduct } from '../hooks/useProducts';

export default function ProductViewPage() {
    const { id } = useParams();
    const { isLoading, setIsLoading } = useLoading();
    const { product, loading: productLoading, error } = useProduct(id);

    useEffect(() => {
        setIsLoading(productLoading);
    }, [productLoading, setIsLoading]);

    if (error) {
        return <div className="p-6 text-red-600">{error}</div>;
    }

    if (!product) {
        return <div className="p-6 text-gray-500">Product not found</div>;
    }

    return (
        <div className="flex flex-col lg:flex-row w-full p-6 gap-6 h-full">
            <div className="flex-1 bg-white rounded-xl shadow-xl p-6 flex flex-col">
                <img
                    src={product.image || "/logo192.png"}
                    alt={product.name}
                    className="w-full h-80 object-contain rounded-lg mb-4"
                />
                <h1 className="text-2xl font-bold text-primaryText mb-2">{product.name}</h1>

                <span className="text-xl font-bold text-[var(--color-secondaryText)] mb-4">{product.price} €</span>

                <div className="flex-1 bg-gray-100 text-primaryText rounded-lg p-4 mb-4">
                    <p className="text-base">
                        {product.description || "No description available."}
                    </p>
                </div>

                <div className="flex justify-center">
                    <button className="w-40 px-4 py-2 bg-[var(--color-secondaryText)] hover:bg-[var(--color-primaryText)] text-white rounded-lg transition-colors duration-200 text-xl cursor-pointer">
                        Buy Now
                    </button>
                </div>
            </div>
            <div className="flex-1 bg-gray-100 rounded-xl shadow-inner flex items-center justify-center p-4">
                <span className="text-gray-500 text-lg">💬 Chat coming soon...</span>
            </div>
        </div>
    );
}
