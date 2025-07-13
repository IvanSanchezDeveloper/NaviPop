import { useLoading } from "../contexts/LoadingContext";

export default function Loading() {
    const { isLoading } = useLoading();

    if (!isLoading) return null;

    return (
        <div className="flex justify-center items-center py-8">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[var(--color-primaryText)]" />
        </div>
    );
}