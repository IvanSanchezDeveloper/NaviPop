import { useState } from 'react';
import {useNavigate} from "react-router-dom";
import axios from '../../api/axiosInstance.jsx'

export default function ProductForm() {
    const [name, setName] = useState('');
    const [price, setPrice] = useState('');
    const [image, setImage] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [dragActive, setDragActive] = useState(false);
    const navigate = useNavigate();

    const validateImage = (file) => {
        const allowedTypes = ['image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes

        if (!allowedTypes.includes(file.type)) {
            return 'Only JPEG and PNG allowed';
        }

        if (file.size > maxSize) {
            return 'File cannot be greater than 5MB';
        }

        return null;
    };

    const validateForm = () => {
        if (!image) {
            return 'You must add an image';
        }

        return null;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        const validationError = validateForm();
        if (validationError) {
            setError(validationError);
            return;
        }

        setIsSubmitting(true);

        try {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('price', price);
            formData.append('image', image);

            const response = await axios.post(`/product/new`, formData);

            if (response.status === 200) {
                navigate('/');
            }

        } catch (err) {
            if (err.response && err.response.data && err.response.data.error) {
                setError(err.response.data.error);
            } else {
                setError('Failed to create product. Please try again.');
            }

        } finally {
            setIsSubmitting(false);
        }
    };

    const handlePriceChange = (e) => {
        const value = e.target.value;
        if (value === '' || /^\d*\.?\d*$/.test(value)) {
            setPrice(value);
        }
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const validationError = validateImage(file);
            if (validationError) {
                setError(validationError);
                e.target.value = '';
                return;
            }
            setError('');
            setImage(file);

            // Create preview URL
            const reader = new FileReader();
            reader.onload = (e) => {
                setImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        const file = e.dataTransfer.files[0];
        if (file) {
            const validationError = validateImage(file);
            if (validationError) {
                setError(validationError);
                return;
            }
            setError('');
            setImage(file);

            const reader = new FileReader();
            reader.onload = (e) => {
                setImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(true);
    };

    const handleDragLeave = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
    };

    const removeImage = () => {
        setImage(null);
        setImagePreview(null);
        setError('');
    };

    return (
        <div className="w-full max-w-4xl p-8 rounded-xl shadow-md bg-primary overflow-y-auto">
            <h2 className="text-3xl font-bold text-left text-primaryText mb-8">
                Create Product
            </h2>

            <form onSubmit={handleSubmit} className="flex gap-8 items-stretch">
                <div className="flex-1 space-y-6 flex flex-col">
                    <input
                        type="text"
                        placeholder="Product"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        required
                        disabled={isSubmitting}
                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                    />

                    <div className="relative">
                        <input
                            type="text"
                            placeholder="Price"
                            value={price}
                            onChange={handlePriceChange}
                            required
                            disabled={isSubmitting}
                            className="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondaryText focus:outline-none bg-white text-primaryText"
                        />
                        <div className="absolute right-3 top-1/2 transform -translate-y-1/2 text-primaryText font-medium">
                            €
                        </div>
                    </div>

                    {error && (
                        <div className="text-sm font-medium text-error">{error}</div>
                    )}

                    <button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full py-3 rounded-lg border border-gray-300 bg-white text-primaryText hover:ring-2 hover:ring-secondaryText transition cursor-pointer"
                    >
                        {isSubmitting ? (
                            <span className="flex items-center justify-center">
                                <div className="animate-spin h-6 w-6 border-2 border-gray-500 rounded-full border-t-transparent" />
                            </span>
                        ) : (
                            'Create Product'
                        )}
                    </button>
                </div>

                <div className="flex-1">
                    <div
                        className={`w-full h-full border-2 border-dashed rounded-lg flex flex-col items-center justify-center cursor-pointer transition relative
                        ${
                            dragActive
                                ? 'border-secondaryText bg-gray-50'
                                : image
                                    ? 'border-green-500 bg-green-50'
                                    : 'border-gray-300 hover:border-secondaryText hover:bg-gray-50'
                        }`}
                        onDrop={handleDrop}
                        onDragOver={handleDragOver}
                        onDragLeave={handleDragLeave}
                        onClick={() => document.getElementById('image-upload').click()}
                    >
                        {image ? (
                            <>
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        removeImage();
                                    }}
                                    className="absolute top-2 right-2 z-10 w-8 h-8 text-error hover:text-[#e85c5c] flex items-center justify-center text-5xl font-bold transition-colors"
                                >
                                    ×
                                </button>

                                <div className="text-center p-4 w-full h-full flex flex-col">
                                    {/* Image preview container with fixed size */}
                                    <div className="flex-1 flex items-center justify-center mb-4 min-h-0">
                                        <div className="w-full h-full max-w-[200px] max-h-[200px] border-2 border-gray-200 rounded-lg overflow-hidden">
                                            <img
                                                src={imagePreview}
                                                alt="Preview"
                                                className="w-full h-full object-cover"
                                            />
                                        </div>
                                    </div>

                                    {/* Image info */}
                                    <div className="flex-shrink-0">
                                        <p className="text-green-600 font-medium mb-2">
                                            Image loaded
                                        </p>
                                        <p className="text-sm text-secondaryText mb-2 truncate">
                                            {image.name}
                                        </p>
                                    </div>
                                </div>
                            </>
                        ) : (
                            <div className="text-center">
                                <div className="text-6xl text-gray-400 mb-4">📷</div>
                                <p className="text-primaryText font-medium mb-2">
                                    Drop image here or click to upload
                                </p>
                                <p className="text-sm text-secondaryText">
                                    JPEG, PNG up to 5MB
                                </p>
                            </div>
                        )}
                        <input
                            id="image-upload"
                            type="file"
                            accept="image/jpeg,image/png"
                            onChange={handleImageChange}
                            disabled={isSubmitting}
                            className="hidden"
                        />
                    </div>
                </div>
            </form>
        </div>
    );
}