import { useNavigate } from "react-router-dom";

export function LoadingCard() {
    return (
        <div className="h-[240px] w-full bg-white rounded-xl shadow-2xl p-4 flex flex-col animate-pulse">
            <div className="h-2/3 w-full bg-gray-100 rounded-md mb-2 flex flex-col items-center justify-center gap-2">
                <div className="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-blue-500"></div>
                <span className="text-xs text-gray-500">Loading...</span>
            </div>

            <div className="h-4 bg-gray-200 rounded mb-2"></div>
            <div className="h-4 bg-gray-200 rounded w-1/3 mt-auto"></div>
        </div>
    );
}

export function PlaceholderCard({ type = 'content' }) {
    if (type === 'empty') {
        return (
            <div className="h-[240px] w-full bg-white rounded-xl shadow-2xl p-4 opacity-30" />
        );
    }

    return (
        <div className="h-[240px] w-full bg-white rounded-xl shadow-2xl p-4 flex flex-col opacity-70">
            <div className="h-2/3 w-full flex items-center justify-center rounded-md mb-2 bg-gray-10">
                <img
                    src="/logo192.png"
                    alt="Placeholder"
                    className="h-16 w-16 object-contain"
                />
            </div>
            <h2 className="text-sm font-semibold text-primaryText truncate">Placeholder Name</h2>
            <span className="text-sm text-primaryText font-bold mt-auto">0.00 €</span>
        </div>
    );
}

export function AddProductCard({ onClick }) {
    return (
        <div
            onClick={onClick}
            className="h-[240px] w-full bg-white border-2 border-dashed border-secondaryText text-secondaryText text-5xl font-bold rounded-xl shadow-2xl flex items-center justify-center cursor-pointer hover:bg-gray-50 hover:-translate-y-1 active:translate-y-0 active:shadow-lg transition-all duration-200"
        >
            +
        </div>
    );
}

export function ProductCard({ product }) {
    const navigate = useNavigate();

    const goToProduct = () => {
        navigate(`/product/${product.id}`);
    };

    return (
        <div
            onClick={goToProduct}
            className="h-[240px] w-full bg-white rounded-xl shadow-2xl p-4 flex flex-col
                       hover:-translate-y-1 hover:shadow-[0_10px_25px_rgba(0,0,0,0.15)]
                       active:translate-y-0 active:shadow-lg
                       transition-all duration-200 cursor-pointer"
        >
            <div className="h-2/3 w-full flex items-center justify-center rounded-md mb-2 bg-gray-10">
                <img
                    src={product.image || "/logo192.png"}
                    alt={product.name}
                    className={
                        product.image
                            ? "h-full w-full object-cover rounded-md mb-2"
                            : "h-16 w-16 object-cover rounded-md mb-2"
                    }
                />
            </div>
            <h2 className="text-sm font-semibold truncate text-primaryText">{product.name}</h2>
            <span className="text-sm text-secondaryText font-bold mt-auto">{product.price} €</span>
        </div>
    );
}