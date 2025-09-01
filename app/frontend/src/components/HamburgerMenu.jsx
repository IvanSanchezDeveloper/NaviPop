import { HiOutlineMenu, HiOutlineX } from 'react-icons/hi';

export const HamburgerToggle = ({ isOpen, onClick }) => (
    <button
        onClick={onClick}
        className="text-[var(--color-primaryText)] hover:text-[var(--color-secondaryText)] text-3xl transition-colors duration-200 focus:outline-none md:hidden"
    >
        {isOpen ? <HiOutlineX /> : <HiOutlineMenu />}
    </button>
);

export const HamburgerMenu = ({ isOpen, children }) => {
    if (!isOpen) return null;
    return <div className="px-4 pb-4 md:hidden">{children}</div>;
};