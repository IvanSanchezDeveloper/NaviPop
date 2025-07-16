import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'

import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage.jsx';
import HomePage from './pages/HomePage.jsx';
import CreateProductPage from './pages/CreateProductPage.jsx';
import RequireAuth from './components/RequireAuth.jsx'
import PublicRoute from './components/PublicRoute';
import AppLayout from './layouts/AppLayout.jsx'
import { AuthProvider } from './contexts/AuthContext';
import { LoadingProvider } from './contexts/LoadingContext';

function App() {
  return (
      <LoadingProvider>
        <AuthProvider>
          <Router>
              <Routes>
                  <Route element={<AppLayout />}>
                    {/* Public route */}
                      <Route element={<PublicRoute />}>
                          <Route path="/login" element={<LoginPage />} />
                          <Route path="/register" element={<RegisterPage />} />
                      </Route>

                      {/* Protected routes */}
                    <Route element={<RequireAuth/>}>
                        <Route path="/" element={<HomePage/>}/>
                        <Route path="/product/new" element={<CreateProductPage/>}/>

                        {/* Catch all for undefined routes */}
                        <Route path="*" element={<Navigate to="/" />}/>
                    </Route>
                  </Route>
              </Routes>
          </Router>
        </AuthProvider>
      </LoadingProvider>
  )
}

export default App
