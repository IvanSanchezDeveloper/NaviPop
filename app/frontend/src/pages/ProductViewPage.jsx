import { useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import axios from "../api/axiosInstance.jsx";
import { useLoading } from '../contexts/LoadingContext';

export default function ProductViewPage() {
    const { id } = useParams();
    const [product, setProduct] = useState(null);
    const { isLoading, setIsLoading } = useLoading();
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchProduct = async () => {
            try {
                setIsLoading(true);
                setError(null);

                const { data } = await axios.get(`/products/${id}`);

                if (data.success && data.data) {
                    setProduct(data.data);
                } else {
                    setError(data.error || "Failed to load product");
                    setProduct(null);
                }
            } catch (err) {
                setError("Error loading product");
                setProduct(null);
            } finally {
                setIsLoading(false);
            }
        };
        fetchProduct();
    }, [id]);

    if (error) {
        return <div className="p-6 text-red-600">{error}</div>;
    }

    if (!product) {
        return <div className="p-6 text-gray-500">Product not found</div>;
    }

    return (
        <div className="flex flex-col lg:flex-row w-full p-6 gap-6 h-full">
            <div className="flex-1 bg-gray-100 rounded-xl shadow-inner flex items-center justify-center p-4">
                <span className="text-gray-500 text-lg">💬 Chat coming soon...</span>
            </div>

            <div className="flex-1 bg-white rounded-xl shadow-xl p-6 flex flex-col">
                <img
                    src={product.image}
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
        </div>
    );
}
