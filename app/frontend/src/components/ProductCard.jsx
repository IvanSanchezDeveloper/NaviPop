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
            <h2 className="text-sm font-semibold text-gray-400 truncate">Placeholder Name</h2>
            <span className="text-sm text-gray-400 font-bold mt-auto">0.00 €</span>
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
    return (
        <div className="h-[240px] w-full bg-white rounded-xl shadow-2xl p-4 flex flex-col hover:-translate-y-1 hover:shadow-[0_10px_25px_rgba(0,0,0,0.15)] active:translate-y-0 active:shadow-lg transition-all duration-200 cursor-pointer">
            <img
                src={product.image || '/react.svg'}
                alt={product.title}
                className="h-2/3 w-full object-cover rounded-md mb-2"
            />
            <h2 className="text-sm font-semibold truncate text-primaryText">{product.title}</h2>
            <p className="text-xs text-gray-600 truncate">{product.description}</p>
            <span className="text-sm text-secondaryText font-bold mt-auto">{product.price} €</span>
        </div>
    );
}