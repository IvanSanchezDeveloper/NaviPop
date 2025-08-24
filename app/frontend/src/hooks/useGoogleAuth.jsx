import { useState } from "react";
import { useAuth } from "../contexts/AuthContext";
export const useGoogleAuth = (backendUrl, setError) => {

    const { setGoogleLoginCookie } = useAuth();

    const startGoogleAuth = () => {
        const googleLoginUrl = `${backendUrl}/api/login/google`;

        const popup = window.open(googleLoginUrl, "googleLogin", "width=500,height=600");

        const listener = async (event) => {
            if (event.origin !== backendUrl) return;
            if (!event.data) return;

            try {
                if (event.data.oneTimeCode) {
                    const result = setGoogleLoginCookie(event.data.oneTimeCode);
                    if (!result.success) setError(result.error);
                } else if (event.data.error) {
                    setError(event.data.error);
                }
            } catch (err) {
                setError(err.message || "Unexpected error during Google login");
            } finally {
                window.removeEventListener("message", listener);
            }
        };

        window.addEventListener("message", listener);

        setTimeout(() => {
            if (popup && !popup.closed) {
                popup.close();
                window.removeEventListener("message", listener);
                setError("Timeout: Google login process timed out. Please try again.");
            }
        }, 150000); // 2.5 minutes

    };

    return { startGoogleAuth };
};
