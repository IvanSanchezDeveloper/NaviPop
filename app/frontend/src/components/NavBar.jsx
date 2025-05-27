import { useEffect, useState } from 'react';
import { FaGithub } from 'react-icons/fa';
import { HiOutlineMenu, HiOutlineX } from 'react-icons/hi';
import { Link } from 'react-router-dom';

export default function NavBar() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isCompactView, setIsCompactView] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            setIsCompactView(window.innerWidth < 700);
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    return (
        <nav className="sticky top-0 left-0 right-0 z-50 bg-[var(--color-primary)] shadow-md backdrop-blur-sm">
            <div className="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 py-8">
                <div className="text-[var(--color-primaryText)] font-bold text-4xl flex-shrink-0">
                    NAVIPop
                </div>

                {isCompactView ? (
                    <button
                        onClick={() => setIsMenuOpen(!isMenuOpen)}
                        className="absolute left-1/2 -translate-x-1/2 text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] text-3xl transition-colors duration-200 focus:outline-none">
                        {isMenuOpen ? <HiOutlineX/> : <HiOutlineMenu/>}
                    </button>
                ) : (
                    <div className="absolute left-1/2 transform -translate-x-1/2 flex space-x-6 text-2xl">
                        <Link
                            to="/"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200 font-semibold"
                        >
                            Home
                        </Link>
                        <Link
                            to="/placeholder"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200"
                        >
                            Placeholder
                        </Link>
                        <Link
                            to="/placeholder2"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200"
                        >
                            Placeholder
                        </Link>
                    </div>
                )}

                <div className="flex-shrink-0">
                    <a
                        href="https://github.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] text-4xl transition-colors duration-200"
                    >
                        <FaGithub/>
                    </a>
                </div>
            </div>

            {/* Burger menu if width is too low */}
            {isCompactView && isMenuOpen && (
                <div className="px-4 pb-4">
                    <div className="flex flex-col space-y-2 text-lg">
                        <Link
                            to="/"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200 font-semibold"
                        >
                            Home
                        </Link>
                        <Link
                            to="/placeholder"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200"
                        >
                            Placeholder
                        </Link>
                        <Link
                            to="/placeholder2"
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200"
                        >
                            Placeholder
                        </Link>
                    </div>
                </div>

            )}
        </nav>
    );
}
