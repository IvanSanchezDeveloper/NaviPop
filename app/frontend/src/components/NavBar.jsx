import { useEffect, useState } from 'react';
import { FaGithub } from 'react-icons/fa';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from "../contexts/AuthContext";
import { HamburgerMenu, HamburgerToggle } from './HamburgerMenu.jsx';

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
    >{children}</Link>
);

const Logo = ({ onClick }) => (
    <Link to="/" onClick={onClick} className="flex items-center text-[var(--color-primaryText)] font-bold text-4xl flex-shrink-0">
        <img src="/logo.png" alt="Logo" className="h-10 w-10 object-contain" />
        <span className="ml-2 leading-none">NAVIPop</span>
    </Link>
);

const Navigation = ({ links, onLinkClick, className }) => (
    <nav className={className}>
        {links.map((link) => (
            <NavLink key={link.to} to={link.to} isHome={link.isHome} onClick={onLinkClick}>
                {link.label}
            </NavLink>
        ))}
    </nav>
);

const AuthActions = ({ user, onAuthAction, className }) => (
    <div className={className}>
        {user && <span className="text-[var(--color-primaryText)] text-xl">{user.email}</span>}
        <button
            onClick={onAuthAction}
            className="w-25 px-4 py-2 bg-[var(--color-secondaryText)] hover:bg-[var(--color-primaryText)] text-white rounded-lg transition-colors duration-200 text-xl cursor-pointer"
        >
            {user ? 'Logout' : 'Login'}
        </button>
    </div>
);

const GitHubLink = () => (
    <a
        href="https://github.com/IvanSanchezDeveloper"
        target="_blank"
        rel="noopener noreferrer"
        className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] text-4xl transition-colors duration-200"
    >
        <FaGithub />
    </a>
);

export default function NavBar() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [isCompactView, setIsCompactView] = useState(() => window.innerWidth < 768);
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const closeMenu = () => setIsMenuOpen(false);

    useEffect(() => {
        const handleResize = () => {
            const compact = window.innerWidth < 768;
            if (!compact) {
                closeMenu();
            }
            setIsCompactView(compact);
        };

        window.addEventListener('resize', handleResize);
        handleResize();
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
        <header className="bg-[var(--color-primary)] shadow-md backdrop-blur-sm sticky top-0 z-50">
            <div className="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 py-4">

                <Logo/>

                {!isCompactView && (
                    <>
                        <div className="absolute left-1/2 transform -translate-x-1/2">
                            <Navigation links={navigationLinks} className="flex space-x-6 text-2xl" />
                        </div>
                        <div className="flex items-center space-x-4">
                            <AuthActions user={user} onAuthAction={handleAuthAction} className="flex items-center space-x-4" />
                            <GitHubLink />
                        </div>
                    </>
                )}

                {isCompactView && (
                    <div className="flex items-center space-x-4">
                        <HamburgerToggle isOpen={isMenuOpen} onClick={() => setIsMenuOpen((prev) => !prev)} />
                        <GitHubLink />
                    </div>
                )}
            </div>

            <HamburgerMenu isOpen={isMenuOpen}>
                <div className="flex flex-col space-y-6 pt-6">
                    <AuthActions
                        user={user}
                        onAuthAction={handleAuthAction}
                        className="flex flex-col items-start space-y-4"
                    />
                    <Navigation
                        links={navigationLinks}
                        className="flex flex-col space-y-2 text-lg"
                    />
                </div>
            </HamburgerMenu>

        </header>
    );
}
