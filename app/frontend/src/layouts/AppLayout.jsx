import NavBar from '../components/NavBar.jsx';
import { Outlet } from 'react-router-dom';
import Loading from "../components/Loading";
import { useLoading } from "../contexts/LoadingContext";

export default function AppLayout() {
    const { isLoading } = useLoading();

    return (
        <div className="grid min-h-screen grid-rows-[auto_1fr_auto] bg-white text-[var(--color-primaryText)]">
            <header className="sticky top-0 z-50">
                <NavBar />
            </header>

            <main className="flex items-center justify-center px-4">
                <div className="w-full h-full relative">
                    <Outlet />
                    {isLoading && (
                        <div className="absolute inset-0 flex justify-center items-center bg-white z-40">
                            <Loading />
                        </div>
                    )}
                </div>
            </main>

            <footer className="bg-[var(--color-secondary)] text-white p-4 text-center">
                Footer aquí
            </footer>
        </div>
    );
}
