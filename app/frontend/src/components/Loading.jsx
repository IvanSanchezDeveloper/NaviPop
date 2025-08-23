import { useLoading } from "../contexts/LoadingContext";

export default function Loading() {
    const { isLoading } = useLoading();

    if (!isLoading) return null;

    return (
        <div className="flex h-full justify-center items-center py-8">
            <div className="h-2/3 w-full rounded-md mb-2 flex flex-col items-center justify-center gap-2">
                <div className="animate-spin rounded-full h-10 w-10 border-2 border-gray-300 border-t-blue-500"></div>
                <span className="text-xl text-gray-500">Loading...</span>
            </div>
        </div>
    );
}