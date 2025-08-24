import { useEffect, useState } from 'react';
import { FaGithub } from 'react-icons/fa';
import { HiOutlineMenu, HiOutlineX } from 'react-icons/hi';
import { Link } from 'react-router-dom';
import { useAuth } from "../contexts/AuthContext";
import { useNavigate } from 'react-router-dom';

const navigationLinks = [
    { to: '/', label: 'Home', isHome: true },
    { to: '/placeholder', label: 'Placeholder' },
    { to: '/placeholder2', label: 'Placeholder' }
];

const NavLink = ({ to, children, isHome = false, onClick }) => (
    <Link
        to={to}
        onClick={onClick}
        className={`text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] transition-colors duration-200 ${
            isHome ? 'font-semibold' : ''
        }`}
    >
        {children}
    </Link>
);

export default function NavBar() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isCompactView, setIsCompactView] = useState(() => window.innerWidth < 700);
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        const handleResize = () => {
            setIsCompactView(window.innerWidth < 700);
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const handleAuthAction = () => {
        if (user) {
            logout();
        } else {
            navigate('/login');
        }
    };

    return (
        <nav className="bg-[var(--color-primary)] shadow-md backdrop-blur-sm">
            <div className="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 py-4">

                <div className="flex items-center text-[var(--color-primaryText)] font-bold text-4xl flex-shrink-0">
                    <img
                        src="/logo.png"
                        alt="Logo"
                        className="h-10 w-10 object-contain"
                    />
                    <span className="ml-2 leading-none">NAVIPop</span>
                </div>

                {!isCompactView && (
                    <div className="absolute left-1/2 transform -translate-x-1/2 flex space-x-6 text-2xl">
                        {navigationLinks.map((link) => (
                            <NavLink
                                key={link.to}
                                to={link.to}
                                isHome={link.isHome}
                            >
                                {link.label}
                            </NavLink>
                        ))}
                    </div>
                )}


                <div className="flex items-center space-x-4">
                    {isCompactView && (
                        <button
                            onClick={() => setIsMenuOpen(!isMenuOpen)}
                            className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] text-3xl transition-colors duration-200 focus:outline-none">
                            {isMenuOpen ? <HiOutlineX/> : <HiOutlineMenu/>}
                        </button>
                    )}

                    {user && !isCompactView && (
                        <span className="text-[var(--color-primaryText)] text-xl">
                            {user.email}
                        </span>
                    )}

                    {!isCompactView && (
                        <button
                            onClick={handleAuthAction}
                            className="w-25 px-4 py-2 bg-[var(--color-secondaryText)] hover:bg-[var(--color-primaryText)] text-white rounded-lg transition-colors duration-200 text-xl cursor-pointer"
                        >
                            {user ? 'Logout' : 'Login'}
                        </button>
                    )}


                    <a
                        href="https://github.com/IvanSanchezDeveloper"
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
                    {user && (
                        <span className="text-[var(--color-primaryText)] text-xl mb-4 block ">
                            {user.email}
                        </span>
                    )}
                    <button
                        onClick={handleAuthAction}
                        className="w-25 px-4 py-2 bg-[var(--color-secondaryText)] hover:bg-[var(--color-primaryText)] text-white rounded-lg transition-colors duration-200 text-xl cursor-pointer mb-4"
                    >
                        {user ? 'Logout' : 'Login'}
                    </button>
                    <div className="flex flex-col space-y-2 text-lg">
                        {navigationLinks.map((link) => (
                            <NavLink
                                key={link.to}
                                to={link.to}
                                isHome={link.isHome}
                            >
                                {link.label}
                            </NavLink>
                        ))}
                    </div>
                </div>
            )}
        </nav>
    );
}
