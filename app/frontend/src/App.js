import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import LoginCallback from './pages/LoginCallback';

export default function App() {
  return (
      <Router>
        <Routes>
          <Route path="/" element={<LoginPage />} />
          <Route path="/login/callback" element={<LoginCallback />} />
          {/* Add routes here */}
        </Routes>
      </Router>
  );
}
