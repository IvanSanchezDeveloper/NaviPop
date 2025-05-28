import NavBar from '../components/NavBar.jsx';
import { Outlet } from 'react-router-dom';

export default function AppLayout() {
    return (
        <div className="grid min-h-screen grid-rows-[auto_1fr_auto] bg-white text-[var(--color-primaryText)]">
            <header>
                <NavBar />
            </header>

            <main className="flex items-center justify-center overflow-auto px-4">
                <div className="w-full max-w-7xl">
                    <Outlet />
                </div>
            </main>

            <footer className="bg-[var(--color-secondary)] text-white p-4 text-center">
                Footer aquí
            </footer>
        </div>
    );
}
