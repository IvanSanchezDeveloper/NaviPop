
export default function Pagination({ page, totalPages, onPageChange }) {
    return (
        <div className="flex items-center justify-center gap-4 mt-8 mb-6">
            <div className="bg-white shadow-md rounded-md px-4 py-2 flex items-center gap-4">
                <button
                    disabled={page === 1}
                    onClick={() => onPageChange(Math.max(page - 1, 1))}
                    className="px-3 py-1 text-sm bg-secondary text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-[var(--color-primaryText)] cursor-pointer"
                >
                    Previous
                </button>
                <span className="text-sm text-primaryText font-medium">
                    Page {page} of {totalPages}
                </span>
                <button
                    disabled={page === totalPages}
                    onClick={() => onPageChange(Math.min(page + 1, totalPages))}
                    className="px-3 py-1 text-sm bg-secondary text-white rounded disabled:opacity-50 disabled:cursor-not-allowed hover:enabled:bg-[var(--color-primaryText)] cursor-pointer"
                >
                    Next
                </button>
            </div>
        </div>
    );
}