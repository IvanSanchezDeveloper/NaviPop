
import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import {useLoading} from "../contexts/LoadingContext.jsx";

function PublicRoute() {
    const { user } = useAuth();

    if (user) {
        return <Navigate to="/" />;
    }

    return <Outlet />;
}

export default PublicRoute;