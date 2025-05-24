import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import LoginCallback from './pages/LoginCallback';
import RequireAuth from './components/RequireAuthWrapper'

export default function App() {
  return (
      <Router>
        <Routes>
            {/* Public route */}
            <Route path="/" element={<LoginPage />} />
            <Route path="/login/callback" element={<LoginCallback />} />

            {/* Protected routes */}
            <Route element={<RequireAuth />}>

            </Route>

            {/* Catch all for undefined routes */}
            <Route path="*" element={<Navigate to="/" replace />} />

        </Routes>
      </Router>
  );
}
