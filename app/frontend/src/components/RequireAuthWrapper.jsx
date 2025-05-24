import { useLocation, Navigate, Outlet } from "react-router-dom";

function RequireAuth() {
  const token = localStorage.getItem("jwt");
  const location = useLocation();

  if (!token) {
    return <Navigate to="/" state={{ from: location }} replace />;
  }

  return <Outlet />;  // renders child routes
}

export default RequireAuth;
